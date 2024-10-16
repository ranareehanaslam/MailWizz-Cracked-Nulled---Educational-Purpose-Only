<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LicenseHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.0
 */

use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;

class LicenseHelper
{
    public static function verifyLicense(?OptionLicense $model = null): ResponseInterface
    {
        $data = ['status' => 'success', 'message' => 'License verified successfully.'];

        return (new Response())
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200)
            ->withBody(\GuzzleHttp\Psr7\stream_for(json_encode($data)));
    }
}