<?php

/**
 * Manage Import's History
 *
 * @author Pavel Kulbakin <p.kulbakin@gmail.com>
 */
class PMXI_Admin_History extends PMXI_Controller_Admin {

	public function init() {
		parent::init();

	}

	/**
	 * Import's History list
	 */
	public function index() {

		$get            = $this->input->get( array(
			's'        => '',
			'order_by' => 'date',
			'order'    => 'DESC',
			'pagenum'  => 1,
			'perPage'  => 25,
			'id'       => '',
		) );
		$get['pagenum'] = absint( $get['pagenum'] );
		$get['id']      = absint( $get['id'] );
		extract( $get );
		if ( empty( $id ) ) {
			wp_redirect( esc_url_raw( add_query_arg( array( 'page'    => 'pmxi-admin-manage',
			                                                'pmxi_nt' => urlencode( __( 'Import is not specified.', 'wp-all-import-pro' ) ),
			), $this->baseUrl ) ) );
			die();
		}
		$this->data += $get;

		if ( ! in_array( $order_by, array( 'date', 'id', 'run_time', 'type' ) ) ) {
			$order_by = 'date';
		}

		if ( ! in_array( $order, array( 'DESC', 'ASC' ) ) ) {
			$order = 'DESC';
		}

		$by = array( 'import_id' => $id );

		$this->data['import'] = new PMXI_Import_Record();
		$this->data['import']->getById( $id );

		$list = new PMXI_History_List();

		$this->data['list'] = $list->setColumns( $list->getTable() . '.*' )->getBy( $by, "$order_by $order", $pagenum, $perPage, $list->getTable() . '.id' );

		$this->data['page_links'] = paginate_links( array(
			'base'      => add_query_arg( array( 'id' => $id, 'pagenum' => '%#%' ), $this->baseUrl ),
			'format'    => '',
			'prev_text' => __( '&laquo;', 'wp-all-import-pro' ),
			'next_text' => __( '&raquo;', 'wp-all-import-pro' ),
			'total'     => ceil( $list->total() / $perPage ),
			'current'   => $pagenum,
		) );

		$this->render();
	}

	/*
	 * Download import log file
	 *
	 */
	public function log() {

		$nonce = ( ! empty( $_REQUEST['_wpnonce'] ) ) ? $_REQUEST['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, '_wpnonce-download_log' ) ) {
			die( __( 'Security check', 'wp-all-import-pro' ) );
		} else {

			$id = $this->input->get( 'history_id' );

			$import_id = $this->input->get( 'id' );

			$wp_uploads = wp_upload_dir();

			$log_file = wp_all_import_secure_file( $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::LOGS_DIRECTORY, $id ) . DIRECTORY_SEPARATOR . $id . '.html';

			if ( file_exists( $log_file ) ) {
				PMXI_download::xml( $log_file );
			} else {

				wp_redirect( esc_url_raw( add_query_arg( array( 'id'      => $import_id,
				                                                'pmxi_nt' => urlencode( __( 'Log file does not exist.', 'wp-all-import-pro' ) ),
				), $this->baseUrl ) ) );
				die();
			}
		}
	}

	/**
	 * Delete an import
	 */
	public function delete() {

		if ( ! get_current_user_id() or ! current_user_can( PMXI_Plugin::$capabilities ) || ! wp_verify_nonce( ( $_REQUEST['_wpnonce_delete-history'] ?? '' ), 'delete-history' ) ) {
			// This nonce is not valid.
			die( 'Security check' );
		} else {
			$id                 = $this->input->get( 'id' );
			$this->data['item'] = $item = new PMXI_History_Record();
			if ( ! $id or $item->getById( $id )->isEmpty() ) {
				wp_redirectesc_url_raw( ( $this->baseUrl ) );
				die();
			}
			$item->delete();
			wp_redirect( esc_url_raw( add_query_arg( 'pmxi_nt', urlencode( __( 'History deleted', 'wp-all-import-pro' ) ), $this->baseUrl ) ) );
			die();
		}

	}

	/**
	 * Bulk actions
	 */
	public function bulk() {
		check_admin_referer( 'bulk-imports', '_wpnonce_bulk-imports' );
		if ( $this->input->post( 'doaction2' ) ) {
			$this->data['action'] = $action = $this->input->post( 'bulk-action2' );
		} else {
			$this->data['action'] = $action = $this->input->post( 'bulk-action' );
		}
		$this->data['ids']   = $ids = $this->input->post( 'items' );
		$this->data['items'] = $items = new PMXI_History_List();
		if ( empty( $action ) or ! in_array( $action, array( 'delete' ) ) or empty( $ids ) or $items->getBy( 'id', $ids )->isEmpty() ) {
			wp_redirect( esc_url_raw( $this->baseUrl ) );
			die();
		}

		foreach ( $items->convertRecords() as $item ) {
			$item->delete();
		}

		$id = $this->input->get( 'id' );

		wp_redirect( esc_url_raw( add_query_arg( array( 'id'      => $id,
		                                                'pmxi_nt' => urlencode( sprintf( __( '%d %s deleted', 'wp-all-import-pro' ), $items->count(), _n( 'history', 'histories', $items->count(), 'wp-all-import-pro' ) ) ),
		), $this->baseUrl ) ) );
		die();

	}
}
