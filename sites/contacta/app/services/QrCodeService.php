<?php

namespace App\Services;

use Collei\Services\Service;
use Collei\Utils\Arr;
use Collei\Utils\Str;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

/**
 *	This allow reuse of code and funcionality injection.
 *	Basic capabilities available through base service.
 *
 */
class QrCodeService extends Service
{
	public function generate(string $data, int $size = 300, int $margin = 10)
	{
		$result = Builder::create()
			->writer(new PngWriter())
			->writerOptions([])
			->data($data)
			->encoding(new Encoding('UTF-8'))
			->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
			->size($size)
			->margin($margin)
			->roundBlockSizeMode(new RoundBlockSizeModeMargin())
			//->logoPath(__DIR__.'/assets/symfony.png')
			//->labelText('This is the label')
			//->labelFont(new NotoSans(20))
			//->labelAlignment(new LabelAlignmentCenter())
			->build();

		return $result;
	}

}





