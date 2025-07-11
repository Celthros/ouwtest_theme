<?php

class WpaeXmlProcessor {
	const SNIPPET_DELIMITER = '*SNIPPET*';

	/** @var  array */
	protected $tags;

	/** @var  string */
	protected $xml;

	/** @var DOMDocument */
	private $dom;

	private $step = 0;

	/** @var WpaeString */
	private $wpaeString;

	public function __construct( WpaeString $wpaeString ) {
		add_filter( 'wp_all_export_post_process_xml', array( $this, 'wp_all_export_post_process_xml' ), 10, 1 );

		$this->wpaeString = $wpaeString;
	}

	public function process( $xml ) {
		$this->step = 0;

		$xml = $this->preprocessXml( $xml );
		$xml = $this->handleSimpleSnippets( $xml );

		// Add a snippet to trigger a process
		$snippetCount = count( $this->parseSnippetsInString( $xml ) );

		if ( $snippetCount == 0 ) {
			$xml .= "<filler>[str_replace('a','b','c')]</filler>";
		}

		// While we have snippets
		if ( $snippetCount = count( $this->parseSnippetsInString( $xml ) ) ) {

			// $this->step++;
			$xml = '<root>' . $xml . '</root>';
			$this->initVariables( $xml );
			$root = $this->dom->getElementsByTagName( "root" );
			$this->parseElement( $root->item( 0 ) );
			$response = $this->dom->saveXML( $this->dom );

			$xml = $this->cleanResponse( $response );

		}

		$xml = $this->postProcessXml( $xml );
		$xml = $this->decodeSpecialCharacters( $xml );
		$xml = $this->encodeSpecialCharsInAttributes( $xml );

		$xml = str_replace( '**OPENSHORTCODE**', '[', $xml );
		$xml = str_replace( '**CLOSESHORTCODE**', ']', $xml );

		return $this->pretify( $xml );
	}

	/**
	 * @param $xml
	 *
	 * @return mixed
	 */
	public function pretify( $xml ) {
		$xml = '<root>' . $xml . '</root>';
		$this->initVariables( $xml );
		// $root = $this->dom->getElementsByTagName("root");
		// $this->preprocess_attributes($root->item(0));

		return "\n  " . $this->cleanResponse( $this->dom->saveXML( $this->dom ) );
	}

	private function preprocess_attributes( DOMNode $element ) {
		if ( $element->hasAttributes() ) {
			for ( $i = 0; $i < $element->attributes->length; $i ++ ) {
				$element->attributes->item( $i )->nodeValue = $this->sanitizeAttribute( $element->attributes->item( $i )->nodeValue );
			}
		}
		if ( $element->hasChildNodes() ) {
			for ( $i = 0; $i < $element->childNodes->length; $i ++ ) {
				$this->preprocess_attributes( $element->childNodes->item( $i ) );
			}
		}
	}

	private function parseElement( DOMNode $element ) {
		if ( $element->hasAttributes() && $element->nodeValue == '' ) {

			if ( $element->hasChildNodes() ) {
				$has_text_elements = false;
				for ( $i = 0; $i < $element->childNodes->length; $i ++ ) {
					if ( $element->childNodes->item( $i )->nodeType == XML_TEXT_NODE ) {
						$has_text_elements = true;
						break;
					}
				}
				if ( ! $has_text_elements ) {
					$nodeAttributes = $this->getNodeAttributes( $element );
					$snippets       = $this->parseSnippetsInString( $nodeAttributes );
					if ( ! empty( $snippets ) ) {
						$tagValues = array();
						foreach ( $snippets as $snippet ) {
							$wholeValue            = str_replace( "\n", '', $nodeAttributes );
							$isInFunction          = $this->wpaeString->isBetween( $wholeValue, $snippet, '[', ']' );
							$snippetValue          = $this->processSnippet( $snippet, $isInFunction );
							$tagValues[ $snippet ] = $snippetValue;
						}

						// Doing this to replace multiple snippet in the same tag (not to treat them as array and
						// replace the snippet with the first letter of the string
						foreach ( $snippets as $snippet ) {
							if ( isset( $tagValues[ $snippet ] ) ) {
								//$element->nodeValue = str_replace($snippet, $tagValues[$snippet], $element->nodeValue);
								$this->replaceSnippetInAttributes( $element, $snippet, $tagValues[ $snippet ] );
							}
						}
					}
				}
				for ( $i = 0; $i < $element->childNodes->length; $i ++ ) {
					$this->parseElement( $element->childNodes->item( $i ) );
				}
			}
			$textNode = new DOMText( '##FILLER##' );
			$element->appendChild( $textNode );

			return $this->parseElement( $textNode );
		}
		if ( $element->nodeType === XML_TEXT_NODE ) {
			$nodeAttributes = $this->getNodeAttributes( $element->parentNode );

			$snippets = $this->parseSnippetsInString( $element->nodeValue . $nodeAttributes );

			$maxTagValues = 0;
			$tagValues    = array();

			if ( count( $snippets ) > 0 ) {
				if ( count( $snippets ) == 1 ) {
					$snippet      = $snippets[0];
					$isInFunction = $this->wpaeString->isBetween( $nodeAttributes . $element->nodeValue, $snippet, '[', ']' );

					$snippetValues = $this->processSnippet( $snippet, $isInFunction );

					if ( ! is_array( $snippetValues ) ) {
						$element->nodeValue = str_replace( $snippet, $snippetValues, $element->nodeValue );
						$nodeXML            = $this->cloneNode( $element->parentNode, $snippet, $snippetValues );
						$f                  = $this->dom->createDocumentFragment();
						$f->appendXML( $nodeXML );
						$this->parseElement( $f );
						if ( $element->parentNode->parentNode ) {
							$element->parentNode->parentNode->replaceChild( $f, $element->parentNode );
						}
					} else {
						foreach ( $snippetValues as $snippetValue ) {
							$newValueNode            = $element->parentNode->cloneNode( true );
							$newValueNode->nodeValue = str_replace( $snippet, $snippetValue, $newValueNode->nodeValue );
							$this->replaceSnippetInAttributes( $newValueNode, $snippet, $snippetValue );
							$this->elementCdata( $newValueNode );
							$element->parentNode->parentNode->insertBefore( $newValueNode, $element->parentNode );
						}
						$element->parentNode->parentNode->removeChild( $element->parentNode );
					}
				} else if ( count( $snippets ) > 1 ) {
					foreach ( $snippets as $snippet ) {
						$wholeValue   = $nodeAttributes . $element->nodeValue;
						$wholeValue   = str_replace( "\n", '', $wholeValue );
						$isInFunction = $this->wpaeString->isBetween( $wholeValue, $snippet, '[', ']' );
						$snippetValue = $this->processSnippet( $snippet, $isInFunction );

						$tagValues[ $snippet ] = $snippetValue;


						if ( is_array( $tagValues[ $snippet ] ) ) {
							if ( count( $tagValues[ $snippet ] ) > $maxTagValues ) {
								$maxTagValues = count( $tagValues[ $snippet ] );
							}
						}
					}
					//We have arrays
					if ( $maxTagValues > 1 ) {
						for ( $i = 0; $i < $maxTagValues; $i ++ ) {
							$elementClone = $element->parentNode->cloneNode( true );
							$elementValue = $elementClone->nodeValue;

							foreach ( $snippets as $snippet ) {
								// We might have the case that
								// there are arrays but also implodes in the same tag
								if ( is_array( $tagValues[ $snippet ] ) ) {
									if ( isset( $tagValues[ $snippet ][ $i ] ) ) {
										$elementValue = str_replace( $snippet, $tagValues[ $snippet ][ $i ], $elementValue );
										$this->replaceSnippetInAttributes( $elementClone, $snippet, $tagValues[ $snippet ][ $i ] );


									} else {
										$elementValue = str_replace( $snippet, "", $elementValue );
										$this->replaceSnippetInAttributes( $elementClone, $snippet, "" );
									}
								} else {
									$elementValue = str_replace( $snippet, $tagValues[ $snippet ], $elementValue );
									$this->replaceSnippetInAttributes( $elementClone, $snippet, $tagValues[ $snippet ] );
								}
							}
							$elementClone->nodeValue = $elementValue;
							$this->elementCdata( $elementClone );
							$element->parentNode->parentNode->insertBefore( $elementClone, $element->parentNode );
						}
						$element->parentNode->parentNode->removeChild( $element->parentNode );
					} else {
						// Doing this to replace multiple snippet in the same tag (not to treat them as array and
						// replace the snippet with the first letter of the string
						foreach ( $snippets as $snippet ) {
							if ( isset( $tagValues[ $snippet ] ) ) {
								$element->nodeValue = str_replace( $snippet, $tagValues[ $snippet ], $element->nodeValue );
								$this->replaceSnippetInAttributes( $element->parentNode, $snippet, $tagValues[ $snippet ] );
							}
						}
					}
				}
			}
			$this->elementCdata( $element );
		} else {
			if ( $element->hasChildNodes() ) {
				$has_text_elements = false;
				for ( $i = 0; $i < $element->childNodes->length; $i ++ ) {
					if ( $element->childNodes->item( $i )->nodeType == XML_TEXT_NODE ) {
						$has_text_elements = true;
						break;
					}
				}
				if ( ! $has_text_elements ) {
					$nodeAttributes = $this->getNodeAttributes( $element );
					$snippets       = $this->parseSnippetsInString( $nodeAttributes );

					if ( ! empty( $snippets ) ) {
						$tagValues = array();
						foreach ( $snippets as $snippet ) {
							$wholeValue            = str_replace( "\n", '', $nodeAttributes );
							$isInFunction          = $this->wpaeString->isBetween( $wholeValue, $snippet, '[', ']' );
							$snippetValue          = $this->processSnippet( $snippet, $isInFunction );
							$tagValues[ $snippet ] = $snippetValue;
						}

						// Doing this to replace multiple snippet in the same tag (not to treat them as array and
						// replace the snippet with the first letter of the string
						foreach ( $snippets as $snippet ) {
							if ( isset( $tagValues[ $snippet ] ) ) {
								//$element->nodeValue = str_replace($snippet, $tagValues[$snippet], $element->nodeValue);
								$this->replaceSnippetInAttributes( $element, $snippet, $tagValues[ $snippet ] );
							}
						}
					}
				}
				for ( $i = 0; $i < $element->childNodes->length; $i ++ ) {
					$this->parseElement( $element->childNodes->item( $i ) );
				}
			}
		}
	}

	/**
	 * @param $filtered
	 *
	 * @return mixed
	 */
	private function sanitizeFunctionName( $filtered ) {
		$functionName = str_replace( 'array(', '(', substr( $filtered, 0, strpos( $filtered, "(" ) ) );

		return $functionName;
	}

	/**
	 * @param $originalTag
	 *
	 * @return array
	 */
	private function parseSnippetsInString( $originalTag ) {
		$results = array();
		$matches = array();
		preg_match_all( "%(\[[^\]\[]*\])%", $originalTag, $matches );

		$snippets = empty( $matches ) ? array() : array_unique( $matches[0] );

		foreach ( $snippets as $snippet ) {
			$isCdataString = '<![CDATA' . $snippet;

			if ( strpos( $this->xml ?? '', $isCdataString ) === false ) {
				$results[] = $snippet;
			}
		}

		return $results;
	}

	/**
	 * @param $v
	 *
	 * @return string
	 */
	private function maybe_cdata( $v, $hasSnippets = false ) {
		if ( XmlExportEngine::$is_preview ) {
			$v = str_replace( '&amp;', '&', $v );
			$v = htmlspecialchars( $v );
			$v = str_replace( '##lt##', '&lt;', $v );
			$v = str_replace( '##gt##', '&gt;', $v );
		}

		if ( XmlExportEngine::$is_preview && ! XmlExportEngine::$exportOptions['show_cdata_in_preview'] ) {
			return $v;
		}

		$cdataStrategyFactory = new CdataStrategyFactory();

		if ( ! isset( XmlExportEngine::$exportOptions['custom_xml_cdata_logic'] ) ) {
			XmlExportEngine::$exportOptions['custom_xml_cdata_logic'] = 'auto';
		}
		$cdataStrategy      = $cdataStrategyFactory->create_strategy( XmlExportEngine::$exportOptions['custom_xml_cdata_logic'] );
		$is_wrap_into_cdata = $cdataStrategy->should_cdata_be_applied( $this->decodeSpecialCharacters( $v ), $hasSnippets );

		if ( $is_wrap_into_cdata === false ) {
			return $v;
		} else {
			return 'CDATABEGIN' . $v . 'CDATACLOSE';
		}
	}

	/**
	 * @param $filtered
	 *
	 * @return mixed
	 */
	private function sanitizeAttribute( $filtered ) {
		$filtered = str_replace( '&amp;', '&', $filtered );
		$filtered = str_replace( '&', '&amp;', $filtered );
		$filtered = str_replace( "'", '&#x27;', $filtered );
		$filtered = str_replace( '"', '&quot;', $filtered );
		$filtered = str_replace( '<', '&lt;', $filtered );
		$filtered = str_replace( '>', '&gt;', $filtered );

		return $filtered;
	}

	/**
	 * @param $functionName
	 *
	 * @throws WpaeMethodNotFoundException
	 */
	private function checkIfFunctionExists( $functionName ) {
		if ( ! function_exists( $functionName ) && $functionName != 'array' ) {
			// Log the function name before throwing an exception as it's not always logged as part of the exception.
			error_log( 'User supplied function ' . $functionName . ' does not exist.' );
			throw new WpaeMethodNotFoundException( $functionName );
		}
	}

	/**
	 * @param $snippet
	 *
	 * @return mixed
	 */
	private function sanitizeSnippet( $snippet ) {
		$sanitizedSnippet = str_replace( array( '[', ']' ), '', $snippet );
		$sanitizedSnippet = str_replace( '\'', '"', $sanitizedSnippet );

		return $sanitizedSnippet;
	}

	/**
	 * @param $snippet
	 * @param $functionName
	 *
	 * @return mixed
	 */
	private function handleEmptyArgs( $snippet, $functionName ) {

		$argsStr = preg_replace( "%^" . $functionName . "\((.*)\)$%", "$1", $snippet );

		$args_list = explode( ',', $argsStr );

		$new_args_list = [];

		foreach ( $args_list as $args_list_item ) {

			if ( $args_list_item == '' ) {
				$new_args_list[] = "\"\"";
			} else {
				$new_args_list[] = $args_list_item;
			}

		}

		$newArgsStr = implode( ',', $new_args_list );

		return str_replace( $argsStr, $newArgsStr, $snippet );

	}

	/**
	 * @param $xml
	 *
	 * @return mixed
	 */
	private function handleSimpleSnippets( $xml ) {
		preg_match_all( "%(\[[^\]\[]*\])%", $xml, $matches );
		$snippets = empty( $matches ) ? array() : array_unique( $matches[0] );

		$simple_snipets = array();
		preg_match_all( "%(\{[^\}\{]*\})%", $xml, $matches );
		$xpaths = array_unique( $matches[0] );

		if ( ! empty( $xpaths ) ) {
			foreach ( $xpaths as $xpath ) {
				if ( ! in_array( $xpath, $snippets ) ) {
					$simple_snipets[] = $xpath;
				}
			}
		}

		if ( ! empty( $simple_snipets ) ) {
			foreach ( $simple_snipets as $snippet ) {

				$filtered = preg_replace( "%[\{\}]%", "", $snippet );

				//Encode data in attributes
				if ( strpos( $xml, "\"$snippet\"" ) !== false || strpos( $xml, "'$snippet'" ) !== false ) {
					$attributeValue = str_replace( '&amp;', '&', $filtered );
					$attributeValue = str_replace( '&', '&amp;', $attributeValue );
					$attributeValue = str_replace( '\'', '&#x27;', $attributeValue );
					$attributeValue = str_replace( '"', '&quot;', $attributeValue );
					$attributeValue = str_replace( '<', '&lt;', $attributeValue );
					$attributeValue = str_replace( '>', '&gt;', $attributeValue );

					$xml = str_replace( "\"" . $snippet . "\"", "\"" . $attributeValue . "\"", $xml );
					$xml = str_replace( "'" . $snippet . "'", "\"" . $attributeValue . "\"", $xml );
				}

				$filteredEncoded = $this->encodeSpecialCharacters( $filtered );

				$xml = str_replace( $snippet, self::SNIPPET_DELIMITER . $filteredEncoded . self::SNIPPET_DELIMITER, $xml );
			}
		}

		return $xml;
	}

	/**
	 * @param $xml
	 *
	 * @return mixed
	 */
	public function encodeSpecialCharsInAttributes( $xml ) {
		preg_match_all( '/<.*?=["\'](.*?)["\'].*?>/', $xml, $attributes );
		$attributes = $attributes[1];

		foreach ( $attributes as $attribute ) {

			$attribute = trim( $attribute, "'\"" );

			if ( ! $this->wpaeString->isBetween( $xml, $attribute, '<![CDATA[', ']]>' ) ) {

				$xml = str_replace( array(
					'\'' . $attribute . '\'',
					'"' . $attribute . '"',
				), '"' . $attribute . '"', $xml );
			}
		}

		return $xml;
	}

	/**
	 * @param $xml
	 */
	private function initVariables( $xml ) {
		$this->xml = $xml;

		$dom                     = new DOMDocument();
		$dom->recover            = true;
		$dom->preserveWhiteSpace = false;
		$dom->substituteEntities = false;
		$dom->resolveExternals   = false;
		$dom->formatOutput       = true;

		$dom->loadXML( $xml );
		$this->dom = $dom;
	}

	/**
	 * @param $snippet
	 * @param bool $isInFunction
	 *
	 * @return mixed
	 * @throws WpaeInvalidStringException
	 * @throws WpaeMethodNotFoundException
	 */
	private function processSnippet( $snippet, $isInFunction = false ) {

		$sanitizedSnippet = $this->sanitizeSnippet( $snippet );

		// Retrieve the snippets themselves for processing.
		preg_match_all( '/\*SNIPPET\*(.*?)\*SNIPPET\*/', $sanitizedSnippet, $snippetStrings );

		// Sanitize the values to ensure only functions provided by the WPAE configurer are run.
		// This avoids running functions listed in the processed values.
		foreach ( $snippetStrings[0] as $snippetString ) {
			$cleanString = str_replace( WpaeXmlProcessor::SNIPPET_DELIMITER, '', $snippetString );

			// We need to convert all of the placeholders back to actual values.
			// This way the receiving function will get usable data.
			$cleanString = str_replace( 'CDATABEGIN', '<![CDATA[', $cleanString );
			$cleanString = str_replace( 'CDATACLOSE', ']]>', $cleanString );

			$cleanString = str_replace( 'CLOSEBRAKET', ']', str_replace( 'OPENBRAKET', '[', $cleanString ) );
			$cleanString = str_replace( 'CLOSECURVE', '}', str_replace( 'OPENCURVE', '{', $cleanString ) );
			$cleanString = str_replace( 'CLOSECIRCLE', ')', str_replace( 'OPENCIRCLE', '(', $cleanString ) );

			$cleanString = str_replace( '**SINGLEQUOT**', "'", $cleanString );
			$cleanString = str_replace( '**DOUBLEQUOT**', "\"", $cleanString );

			$cleanString = str_replace( '**GT**', ">", $cleanString );
			$cleanString = str_replace( '**LT**', "<", $cleanString );

			$cleanString = $this->decodeSpecialCharacters( $cleanString );

			// We use var_export and html_entity_decode to ensure the strings passed contain the expected data.
			// For non-string values the data could be serialized so we need to unserialize it before passing it to the function.
			$sanitizedSnippet = str_replace( $snippetString, var_export( \maybe_unserialize( html_entity_decode( $cleanString ) ), 'true' ), $sanitizedSnippet );
		}

		$sanitizedSnippet = str_replace( WpaeXmlProcessor::SNIPPET_DELIMITER, '"', $sanitizedSnippet );
		$functionName     = $this->sanitizeFunctionName( $sanitizedSnippet );

		// Failsafe if we no longer have a function to process after sanitizing.
		if ( empty( $functionName ) ) {
			return '';
		}

		$this->checkIfFunctionExists( $functionName );

		$sanitizedSnippet = $this->handleEmptyArgs( $sanitizedSnippet, $functionName );

		$argsStr = preg_replace( "%^" . $functionName . "\((.*)\)$%", "$1", $sanitizedSnippet );

		preg_match_all( "%(\"(?:[^\"\\\\]|\\.|\"\")*\")%", $argsStr, $matches );
		if ( ! empty( $matches[0] ) ) {
			$args = $matches[0];
			foreach ( $args as $k => $arg ) {

				// Ensure $arg doesn't have any rogue double quotes.
				$clean_arg = preg_replace( "%(?!^)\"(?!$)%m", "**DOUBLEQUOT**", $arg );

				$sanitizedSnippet = str_replace( $arg, 'apply_filters("wp_all_export_post_process_xml", ' . $clean_arg . ')', $sanitizedSnippet );
			}
		}

		// Clean empty strings
		$sanitizedSnippet = str_replace( array( '(,', '( ,', ',)', ', )' ), array(
			'(""',
			'(""',
			',"")',
			',"")',
		), $sanitizedSnippet );

		$snippetValue = eval( 'return ' . $sanitizedSnippet . ';' );
		$snippetValue = $this->encodeSpecialCharacters( $snippetValue );

		if ( strpos( $snippet, 'explode' ) !== false && $isInFunction ) {
			$snippetValue = 'array(' . "'" . implode( "','", $snippetValue ) . "'" . ')';
		}

		return $snippetValue;
	}

	public function wp_all_export_post_process_xml( $value ) {
		return $this->postProcessXml( $this->decodeSpecialCharacters( str_replace( '"', '', $value ) ) );
	}

	public function getNodeAttributes( DOMNode $dom ) {
		$result = "";
		if ( $dom->hasAttributes() ) {
			for ( $i = 0; $i < $dom->attributes->length; $i ++ ) {
				$result .= $dom->attributes->item( $i )->nodeValue;
			}
		}

		return $result;
	}

	/**
	 * @param DOMNode $newValueNode
	 * @param $snippet
	 * @param $snippetValue
	 *
	 * @internal param $snippetValues
	 */
	private function replaceSnippetInAttributes( DOMNode $newValueNode, $snippet, $snippetValue ) {
		$snippetValue = $this->sanitizeAttribute( $snippetValue );
		if ( $newValueNode->hasAttributes() ) {
			for ( $i = 0; $i < $newValueNode->attributes->length; $i ++ ) {
				$newValueNode->attributes->item( $i )->nodeValue = str_replace( $snippet, $snippetValue, $newValueNode->attributes->item( $i )->nodeValue );
			}
		}
	}

	/**
	 * @param DOMNode $element
	 */
	private function elementCdata( DOMNode $element ) {
		$hasSnippets = $this->parseSnippetsInString( $element->nodeValue );

		if ( strpos( $element->nodeValue, '<![CDATA[' ) === false && strpos( $element->nodeValue, 'CDATABEGIN' ) === false ) {
			$element->nodeValue = $this->maybe_cdata( $element->nodeValue, $hasSnippets );
		}
	}

	private function encodeSpecialCharacters( $text ) {
		$text = str_replace( '&amp;', '&', $text );
		$text = str_replace( '&', '##amp##', $text );
		$text = str_replace( "'", '##x27##', $text );
		$text = str_replace( '"', '##quot##', $text );
		$text = str_replace( '<', '##lt##', $text );
		$text = str_replace( '>', '##gt##', $text );

		return $text;
	}

	private function decodeSpecialCharacters( $text ) {
		$text = str_replace( '##amp##', '&', $text );
		$text = str_replace( '##x27##', "'", $text );
		$text = str_replace( '##quot##', '"', $text );
		$text = str_replace( '##lt##', '<', $text );
		$text = str_replace( '##gt##', '>', $text );

		return $text;
	}

	/**
	 * @param $xml
	 *
	 * @return mixed
	 */
	private function postProcessXml( $xml ) {
		$xml = str_replace( 'CDATABEGIN', '<![CDATA[', $xml );
		$xml = str_replace( 'CDATACLOSE', ']]>', $xml );

		$xml = str_replace( 'CLOSEBRAKET', ']', str_replace( 'OPENBRAKET', '[', $xml ) );
		$xml = str_replace( 'CLOSECURVE', '}', str_replace( 'OPENCURVE', '{', $xml ) );
		$xml = str_replace( 'CLOSECIRCLE', ')', str_replace( 'OPENCIRCLE', '(', $xml ) );

		$xml = str_replace( '**SINGLEQUOT**', "'", $xml );
		$xml = str_replace( '**DOUBLEQUOT**', "\"", $xml );

		$xml = str_replace( '**GT**', ">", $xml );
		$xml = str_replace( '**LT**', "<", $xml );

		$xml = str_replace( '##FILLER##', '', $xml );
		$xml = str_replace( '<filler>c</filler>', '', $xml );
		$xml = str_replace( '<filler><![CDATA[c]]></filler>', '', $xml );
		$xml = str_replace( '<filler>CDATABEGINcCDATACLOSE</filler>', '', $xml );

		$xml = str_replace( '<commentTempNode>', '<!--', $xml );
		$xml = str_replace( '</commentTempNode>', '-->', $xml );

		$xml = str_replace( self::SNIPPET_DELIMITER, '', $xml );

		$xml = trim( $xml );

		return $xml;
	}

	/**
	 * @param $xml
	 *
	 * @return mixed
	 */
	private function preprocessXml( $xml ) {
		$xml = str_replace( '<!--', '<commentTempNode>', $xml );
		$xml = str_replace( '-->', '</commentTempNode>', $xml );
		$xml = str_replace( "\"{}\"", '""', $xml );

		preg_replace( '%(\[.*)({})(.*\])%', "$1\"\"$2", $xml );

		$xml = str_replace( ">\"\"<", '><', $xml );
		$xml = str_replace( "[implode(',',{})]", "", $xml );

		return $xml;
	}

	/**
	 * @param $xml
	 * @param $response
	 *
	 * @return mixed
	 */
	private function cleanResponse( $response ) {
		$response = str_replace( '<root>', '', $response );
		$response = str_replace( '</root>', '', $response );
		$response = str_replace( '<root/>', '', $response );
		$xml      = str_replace( "<?xml version=\"1.0\"?>", '', $response );
		$xml      = str_replace( "<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "", $xml );

		return trim( $xml );
	}

	/**
	 *
	 * Cloning DOMNode with including child DOMNode elements
	 *
	 * @param $node
	 *
	 * @return DOMNode|string
	 */
	private function cloneNode( DOMNode $node, $snippet, $snippetValues ) {

		$Document                     = new DOMDocument( '1.0', 'UTF-8' );
		$Document->preserveWhiteSpace = false;
		$Document->formatOutput       = true;
		$newElement                   = $Document->importNode( $node, true );

		$this->replaceSnippetInAttributes( $newElement, $snippet, $snippetValues );

		foreach ( $newElement->childNodes as $child ) {
			if ( $child->nodeType === XML_TEXT_NODE ) {
				$this->elementCdata( $child );
			}
		}

		$Document->appendChild( $newElement );

		return trim( str_replace( "<?xml version=\"1.0\" encoding=\"UTF-8\"?>", "", $Document->saveXML() ) );

	}
}