<?php
namespace Sandbox\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;

class PluginsController extends SandboxAppController {

	/**
	 * @var array
	 */
	public $components = [
		'RequestHandler' => [
			'viewClassMap' => ['pdf' => 'CakePdf.Pdf']
		]
	];

	/**
	 * Overview
	 *
	 * @return void
	 */
	public function index() {
	}

	/**
	 * PDF generation examples.
	 *
	 * @see http://www.dereuromark.de/2014/04/08/generating-pdfs-with-cakephp/
	 *
	 * @return void
	 */
	public function cakePdf() {
	}

	/**
	 * Actual PDF test action. Will only be called via .pdf extension.
	 *
	 * To test FOR WINDOWS: make sure you got
	 * - APP/files/wkhtmltopdf/ with the binary files (or any other location)
	 * - Configure::write('CakePdf.binary', APP . 'files\wkhtmltopdf\wkhtmltopdf.exe'); in your configs
	 *
	 * @param string|null $engineSlug
	 * @throws \Cake\Network\Exception\NotFoundException
	 * @return \Cake\Http\Response|null
	 */
	public function pdfTest($engineSlug = null) {
		// This is just so save actions and use this method for all engine tests
		$engines = [
			'dom' => 'DomPdf',
			'wk' => 'WkHtmlToPdf',
			'tc' => 'Tcpdf',
			//'m' => 'Mpdf'
		];
		if (!empty($engineSlug)) {
			if (!Configure::read('debug') && $engineSlug === 'wk') {
				$this->Flash->error('This engine does not work on this server right now, try locally');
				return $this->redirect(['action' => 'cakePdf']);
			}

			if (empty($engines[$engineSlug])) {
				throw new NotFoundException('Invalid engine');
			}
			$engine = $engines[$engineSlug];
			$this->_setPdfConfig($engine);
		} else {
			$this->viewBuilder()->templatePath('Plugins' . DS . 'pdf');
			$this->viewBuilder()->layoutPath('pdf');
		}

		// Setting dynamic config settings
		Configure::write('CakePdf.download', (bool)$this->request->query('download'));
		Configure::write('CakePdf.filename', 'example-' . $engineSlug . '.pdf');

		// Passing some test data to the view
		$someTestArray = ['Foo' => ['bar' => 'value']];
		$this->set(compact('someTestArray'));
	}

	/**
	 * PdfTestController::_setConfig()
	 *
	 * @param string|null $engine
	 * @return void
	 */
	protected function _setPdfConfig($engine = null) {
		if ($engine === null) {
			$engine = 'WkHtmlToPdf';
		}
		$settings = [
			'engine' => 'CakePdf.' . $engine,
			'options' => [
				'print-media-type' => false,
				'outline' => true,
				'dpi' => 96
			],
			'margin' => [
				'bottom' => 15,
				'left' => 50,
				'right' => 30,
				'top' => 45
			],
			'orientation' => 'portrait',
		];
		$settings += (array)Configure::read('CakePdf');
		if ($settings['engine'] === 'CakePdf.WkHtmlToPdf') {
			$settings['engine'] = ['className' => 'CakePdf.WkHtmlToPdf'] + $settings;
		}

		Configure::write('CakePdf', $settings);

		if ($engine === 'DomPdf') {
			define('DOMPDF_ENABLE_REMOTE', true);
		}
	}

}
