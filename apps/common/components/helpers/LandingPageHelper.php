<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageHelper
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.0
 */
class LandingPageHelper
{
    /**
     * This is the pattern used for the campaigns
     * Previous pattern until 2.1.11:  href(\s+)?=(\s+)?(\042|\047)(\s+)?(.*?)(\s+)?(\042|\047)
     * since 2.1.11 - We get only the <a> tags
     * Previous patter until 2.1.16: <a((?!href).*)?(href(\s+)?=(\s+)?(\042|\047)(\s+)?(.*?)(\s+)?(\042|\047))
     * (\042|\047) are octal quotes.
     */
    private const MATCH_HREFS_REGEX = '/<a\s+(?:[^>]*?\s+)?(href(\s+)?=((\s+)?(\042|\047)(.*?)(\042|\047)))/i';
    private const MATCH_HREFS_REGEX_MARKUP_INDEX = 1;
    private const MATCH_HREFS_REGEX_URL_INDEX = 6;

    /**
     * @param LandingPageRevisionVariant $variant
     * @return string
     */
    public static function transformLinksForTracking(LandingPageRevisionVariant $variant): string
    {
        static $trackingUrls = [];
        static $trackingUrlsSaved = [];

        $content = $variant->content;
        // Get the urls from the db that are set for tracking
        if (!($trackingUrlsSavedModels = $variant->urls)) {
            return $content;
        }

        // Get their destinations
        foreach ($trackingUrlsSavedModels as $urlModel) {
            $trackingUrlsSaved[] = $urlModel->destination;
        }

        $content = StringHelper::decodeSurroundingTags($content);
        $content = StringHelper::normalizeUrlsInContent($content);

        /** @var OptionUrl $optionUrl */
        $optionUrl            = container()->get(OptionUrl::class);
        $trackClickUrlSegment = (string)app_param(
            'landing_page.track.click.url.segment',
            MW_LANDING_PAGE_TRACK_CLICK_URL_SEGMENT
        );

        $baseUrl     = $optionUrl->getFrontendUrl();
        $trackingUrl = $baseUrl . 'lp/' . $variant->getHashId() . '/' . $trackClickUrlSegment;

        $pattern = self::MATCH_HREFS_REGEX;

        if (!preg_match_all($pattern, $content, $matches)) {
            return $content;
        }

        $urls = $matches[self::MATCH_HREFS_REGEX_URL_INDEX];
        $urls = array_map('trim', $urls);
        $urls = (array)array_combine($urls, $matches[self::MATCH_HREFS_REGEX_MARKUP_INDEX]);

        // Foreach found url, transform it, if it is the case
        $foundUrls = [];
        foreach ($urls as $url => $markup) {
            $url = StringHelper::normalizeUrl((string)$url);

            if (!in_array($url, $trackingUrlsSaved)) {
                continue;
            }

            // if this url is already transformed for this variant, skip it
            $patternUrl = sprintf(
                '%s/%s/%s',
                $baseUrl . 'lp',
                $variant->getHashId(),
                $trackClickUrlSegment
            );
            $pattern    = '/^(' . preg_quote($patternUrl, '/') . ')([a-f0-9]{40})/i';
            if (preg_match($pattern, $url)) {
                continue;
            }
            //

            if (preg_match('/https?.*/i', $url, $matches) && FilterVarHelper::url($url)) {
                $_url             = trim((string)$matches[0]);
                $foundUrls[$_url] = $markup;
            }
        }

        if (empty($foundUrls)) {
            return $content;
        }
        $prefix = (string)$variant->getHashId();
        $sort   = [];

        foreach ($foundUrls as $url => $markup) {
            $urlHash = sha1($url);
            $track   = $trackingUrl . '/' . $urlHash;
            $length  = strlen($url);

            $trackingUrls[] = [
                'url'    => $url,
                'hash'   => $urlHash,
                'track'  => $track,
                'length' => $length,
                'markup' => $markup,
            ];

            $sort[] = $length;
        }

        unset($foundUrls);

        // make sure we order by the longest url to the shortest
        array_multisort($sort, SORT_DESC, SORT_NUMERIC, $trackingUrls);

        if (!empty($trackingUrls)) {
            $searchReplace = [];
            foreach ($trackingUrls as $urlData) {
                $searchReplace[$urlData['markup']] = 'href="' . $urlData['track'] . '"';
            }

            $content = (string)str_replace(array_keys($searchReplace), array_values($searchReplace), $content);

            // put back link hrefs
            $searchReplace = [];
            foreach ($trackingUrls as $urlData) {
                $searchReplace['link href="' . $urlData['track'] . '"'] = 'link href="' . $urlData['url'] . '"';
            }
            $content = (string)str_replace(array_keys($searchReplace), array_values($searchReplace), $content);

            unset($searchReplace);
        }

        // return transformed
        return $content;
    }

    /**
     * @param string $content
     * @return array
     */
    public static function extractTemplateUrls(string $content): array
    {
        if (empty($content)) {
            return [];
        }

        static $urls = [];

        $content = StringHelper::decodeSurroundingTags($content);
        $content = StringHelper::normalizeUrlsInContent($content);

        $hash = sha1($content);

        if (array_key_exists($hash, $urls)) {
            return $urls[$hash];
        }

        $urls[$hash] = [];

        $pattern = self::MATCH_HREFS_REGEX;
        if (!preg_match_all($pattern, $content, $matches)) {
            return $urls[$hash];
        }
        if (empty($matches[self::MATCH_HREFS_REGEX_URL_INDEX])) {
            return $urls[$hash];
        }

        $urls[$hash] = array_unique(array_map(
            'html_decode',
            array_map('trim', array_map('strval', $matches[self::MATCH_HREFS_REGEX_URL_INDEX]))
        ));

        // remove tag urls
        foreach ($urls[$hash] as $index => $url) {
            $url = StringHelper::normalizeUrl((string)$url);
            if (empty($url) || (strpos($url, '[') !== 0 && !FilterVarHelper::url($url))) {
                unset($urls[$hash][$index]);
            }
        }

        sort($urls[$hash]);

        return $urls[$hash];
    }

    /**
     * @return LandingPageTemplate[]
     */
    public static function getTemplates(): array
    {
        /** @var LandingPageTemplate[] $templates */
        $templates = LandingPageTemplate::model()->findAll('builder_id IS NULL');

        /** @var LandingPageTemplate[] $templates */
        $templates = (array)hooks()->applyFilters('landing_page_templates_grid_items', $templates);

        return $templates;
    }

    /**
     * @param string $content
     * @param LandingPageRevisionVariant $variant
     * @return string
     */
    public static function applyDomainAliasForTrackingLinks(string $content, LandingPageRevisionVariant $variant): string
    {
        $landingPageDomainModel = $variant->revision->page->domain;
        if (empty($landingPageDomainModel)) {
            return $content;
        }

        /** @var OptionUrl $optionUrl */
        $optionUrl = container()->get(OptionUrl::class);

        $currentDomainName  = parse_url($optionUrl->getFrontendUrl(), PHP_URL_HOST);
        $landingPageDomainName = $landingPageDomainModel->getDomainNameWithSchema();
        $landingPageDomainName = parse_url($landingPageDomainName, PHP_URL_HOST);

        if (!empty($currentDomainName) && !empty($landingPageDomainName)) {
            $searchReplace = [
                'https://www.' . $currentDomainName => 'http://' . $landingPageDomainName,
                'http://www.' . $currentDomainName  => 'http://' . $landingPageDomainName,
                'https://' . $currentDomainName     => 'http://' . $landingPageDomainName,
                'http://' . $currentDomainName      => 'http://' . $landingPageDomainName,
            ];

            if (!empty($landingPageDomainModel->scheme) && $landingPageDomainModel->scheme ==  LandingPageDomain::SCHEME_HTTPS) {
                foreach ($searchReplace as $key => $value) {
                    $searchReplace[$key] = (string)str_replace('http://', 'https://', $value);
                }
            }

            if (stripos($landingPageDomainName, $currentDomainName) === false) {
                $searchReplace[$currentDomainName] = $landingPageDomainName;
            }

            $searchFor   = array_keys($searchReplace);
            $replaceWith = array_values($searchReplace);

            $content = (string)str_replace($searchFor, $replaceWith, $content);
        }

        return $content;
    }

    /**
     * @return array
     */
    public static function getTemplatesAsOptions(): array
    {
        $templates = self::getTemplates();

        $options = [];
        foreach ($templates as $template) {
            $options[$template->template_id] = $template->title;
        }
        return $options;
    }
}
