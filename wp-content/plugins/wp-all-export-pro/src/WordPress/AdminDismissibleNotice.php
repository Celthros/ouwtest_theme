<?php

namespace Wpae\WordPress;


class AdminDismissibleNotice extends AdminNotice {
	private $noticeId;

	public function __construct( $message, $noticeId ) {
		parent::__construct( $message );
		$this->noticeId = $noticeId;
	}

	public function showNotice() {
		?>
		<div class="<?php echo esc_attr( $this->getType() ); ?>" style="position: relative;"><p>
				<?php echo wp_kses_post( $this->message ); ?>
			</p>
			<button class="notice-dismiss wpae-general-notice-dismiss" type="button"
			        data-noticeId="<?php echo esc_attr( $this->noticeId ); ?>"><span class="screen-reader-text">Dismiss this notice.</span>
			</button>
		</div>
		<?php
	}

	public function render() {
		add_action( 'admin_notices', array( $this, 'showNotice' ) );
	}

	public function getType() {
		return 'error';
	}

	public function isDismissed() {
		$optionName  = 'wpae_dismiss_warnings_' . $this->noticeId;
		$optionValue = get_option( $optionName, false );

		return $optionValue;
	}
}