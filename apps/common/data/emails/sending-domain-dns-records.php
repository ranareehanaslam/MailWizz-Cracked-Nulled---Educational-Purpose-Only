<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
} ?>
<table>
    <tr>
        <td style="word-wrap: break-word;max-width: 600px;">Please edit your DNS records for <em>[DOMAIN_NAME]</em> domain and add the following TXT record:<br />
            <p><b>[DNS_TXT_DKIM_RECORD]</b></p>
        </td>
    </tr>
    <tr>
        <td style="word-wrap: break-word;max-width: 600px;">For best delivery rates, your domain SPF record must look like:<br />
            <p><b>[DNS_TXT_SPF_RECORD]</b></p>
        </td>
    </tr>
    <tr>
        <td style="word-wrap: break-word;max-width: 600px;">For best delivery rates, your domain DMARC record must look like:<br />
            <p><b>[DNS_TXT_DMARC_RECORD]</b></p>
        </td>
    </tr>
</table>
