<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * Countdown
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.1.15
 */
class Countdown extends \EmailCountdown\DefaultCountdown
{
    const MEDIUM_SIZE = 'medium';
    const SMALL_SIZE = 'small';
    const LARGE_SIZE = 'large';

    /**
     * @var string
     */
    protected $size = self::MEDIUM_SIZE;

    /**
     * @var string
     */
    protected $daysLabel = 'DAYS';

    /**
     * @var string
     */
    protected $hoursLabel = 'HOURS';

    /**
     * @var string
     */
    protected $minutesLabel = 'MINUTES';

    /**
     * @var string
     */
    protected $secondsLabel = 'SECONDS';

    /**
     * @var bool
     */
    protected $showCircle = false;

    /**
     * @var array
     */
    private $circleBackgroundColor = [
        'red'   => 255,
        'green' => 204,
        'blue'  => 204,
    ];

    /**
     * @var array
     */
    private $circleForegroundColor = [
        'red'   => 255,
        'green' => 0,
        'blue'  => 0,
    ];

    /**
     * @var int
     */
    private $circleWidth = 100;

    /**
     * @var int
     */
    private $circleHeight = 100;

    /**
     * @var float
     */
    private $circleScale = 3.0;

    // cache the circle image so don't have to draw it again
    /**
     * @var resource|false
     */
    private $circleImage = false;

    /**
     * @var int
     */
    private $lastDays;

    /**
     * @var int
     */
    private $lastHours;

    /**
     * @var int
     */
    private $lastMinutes;

    /**
     * @var int
     */
    private $circleBackgroundColorAll = 0;

    /**
     * @var int
     */
    private $circleForegroundColorAll = 0;

    /**
     * @param \DateTime|null $destinationTime
     *
     * @return Countdown
     */
    public function setDestinationTime($destinationTime): Countdown
    {
        if ($destinationTime === null) {
            $destinationTime = Carbon\Carbon::now();
        }
        return parent::setDestinationTime($destinationTime);
    }

    /**
     * @param string $size
     * @return Countdown
     */
    public function setSize(string $size): Countdown
    {
        $mapping = $this->getSizeMapping();

        if (!array_key_exists($size, $mapping)) {
            $size = self::MEDIUM_SIZE;
        }

        $sizeSettings = $mapping[$size];

        $this->width        = $sizeSettings['width'];
        $this->height       = $sizeSettings['height'];
        $this->circleHeight = $sizeSettings['circleHeight'];
        $this->circleWidth  = $sizeSettings['circleWidth'];
        $this->circleScale  = $sizeSettings['circleScale'];

        $this->setTextData(...array_merge(['days'], array_values($sizeSettings['textData']['days'])));
        $this->setTextData(...array_merge(['hours'], array_values($sizeSettings['textData']['hours'])));
        $this->setTextData(...array_merge(['minutes'], array_values($sizeSettings['textData']['minutes'])));
        $this->setTextData(...array_merge(['seconds'], array_values($sizeSettings['textData']['seconds'])));

        $this->size = $size;

        return $this;
    }

    /**
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }

    /**
     * set the circle background color
     *
     * @param string $circleBackgroundColor
     * @return $this
     */
    public function setCircleBackgroundColor(string $circleBackgroundColor): Countdown
    {
        if (!empty($circleBackgroundColor) && preg_match('/[0-9a-fA-F]{6}/', $circleBackgroundColor) == 1) {
            $this->circleBackgroundColor = self::convertHexToRGB($circleBackgroundColor);
        }
        return $this;
    }

    /**
     * set the circle foreground color
     *
     * @param string $circleForegroundColor
     * @return $this
     */
    public function setCircleForegroundColor(string $circleForegroundColor): Countdown
    {
        if (!empty($circleForegroundColor) && preg_match('/[0-9a-fA-F]{6}/', $circleForegroundColor) == 1) {
            $this->circleForegroundColor = self::convertHexToRGB($circleForegroundColor);
        }
        return $this;
    }

    /**
     * @param bool $showCircle
     * @return Countdown
     */
    public function setShowCircle(bool $showCircle): Countdown
    {
        $this->showCircle = $showCircle;
        return $this;
    }

    /**
     * @return bool
     */
    public function getShowCircle(): bool
    {
        return $this->showCircle;
    }

    /**
     * @return string
     */
    public function getDaysLabel(): string
    {
        return $this->daysLabel;
    }

    /**
     * @param string $daysLabel
     * @return Countdown
     */
    public function setDaysLabel(string $daysLabel): Countdown
    {
        $this->daysLabel = $daysLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getHoursLabel(): string
    {
        return $this->hoursLabel;
    }

    /**
     * @param string $hoursLabel
     * @return Countdown
     */
    public function setHoursLabel(string $hoursLabel): Countdown
    {
        $this->hoursLabel = $hoursLabel;

        return $this;
    }

    /**
     * @return string
     */
    public function getMinutesLabel(): string
    {
        return $this->minutesLabel;
    }

    /**
     * @param string $minutesLabel
     * @return Countdown
     */
    public function setMinutesLabel(string $minutesLabel): Countdown
    {
        $this->minutesLabel = $minutesLabel;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecondsLabel(): string
    {
        return $this->secondsLabel;
    }

    /**
     * @param string $secondsLabel
     * @return Countdown
     */
    public function setSecondsLabel(string $secondsLabel): Countdown
    {
        $this->secondsLabel = $secondsLabel;

        return $this;
    }

    /**
     * @return array[]
     */
    protected function getSizeMapping(): array
    {
        return [
            self::MEDIUM_SIZE => [
                'width'        => 400,
                'height'       => 100,
                'circleHeight' => 100,
                'circleWidth'  => 100,
                'circleScale'  => 3.0,
                'textData'     => [
                    'days'    => [
                        'label'          => $this->getDaysLabel(),
                        'textSize'       => 32,
                        'textPositionX'  => 50,
                        'textPositionY'  => 62,
                        'labelSize'      => 7,
                        'labelPositionX' => 52,
                        'labelPositionY' => 75,
                    ],
                    'hours'   => [
                        'label'          => $this->getHoursLabel(),
                        'textSize'       => 30,
                        'textPositionX'  => 152,
                        'textPositionY'  => 62,
                        'labelSize'      => 7,
                        'labelPositionX' => 152,
                        'labelPositionY' => 75,
                    ],
                    'minutes' => [
                        'label'          => $this->getMinutesLabel(),
                        'textSize'       => 30,
                        'textPositionX'  => 252,
                        'textPositionY'  => 62,
                        'labelSize'      => 7,
                        'labelPositionX' => 252,
                        'labelPositionY' => 75,
                    ],
                    'seconds' => [
                        'label'          => $this->getSecondsLabel(),
                        'textSize'       => 30,
                        'textPositionX'  => 352,
                        'textPositionY'  => 62,
                        'labelSize'      => 7,
                        'labelPositionX' => 352,
                        'labelPositionY' => 75,
                    ],
                ],
            ],
            self::SMALL_SIZE  => [
                'width'        => 240,
                'height'       => 65,
                'circleHeight' => 65,
                'circleWidth'  => 65,
                'circleScale'  => 5,
                'textData'     => [
                    'days'    => [
                        'label'          => $this->getDaysLabel(),
                        'textSize'       => 16,
                        'textPositionX'  => 33,
                        'textPositionY'  => 70 / 2,
                        'labelSize'      => 6,
                        'labelPositionX' => 33,
                        'labelPositionY' => 70 / 2 + 10,
                    ],
                    'hours'   => [
                        'label'          => $this->getHoursLabel(),
                        'textSize'       => 15,
                        'textPositionX'  => 33 * 2 + 27,
                        'textPositionY'  => 70 / 2,
                        'labelSize'      => 6,
                        'labelPositionX' => 33 * 2 + 27,
                        'labelPositionY' => 70 / 2 + 10,
                    ],
                    'minutes' => [
                        'label'          => $this->getMinutesLabel(),
                        'textSize'       => 15,
                        'textPositionX'  => 33 * 4 + 20,
                        'textPositionY'  => 70 / 2,
                        'labelSize'      => 6,
                        'labelPositionX' => 33 * 4 + 20,
                        'labelPositionY' => 70 / 2 + 10,
                    ],
                    'seconds' => [
                        'label'          => $this->getSecondsLabel(),
                        'textSize'       => 15,
                        'textPositionX'  => 33 * 6 + 15,
                        'textPositionY'  => 70 / 2,
                        'labelSize'      => 6,
                        'labelPositionX' => 33 * 6 + 15,
                        'labelPositionY' => 70 / 2 + 10,
                    ],
                ],
            ],
            self::LARGE_SIZE  => [
                'width'        => 800,
                'height'       => 200,
                'circleHeight' => 200,
                'circleWidth'  => 200,
                'circleScale'  => 1.5,
                'textData'     => [
                    'days'    => [
                        'label'          => $this->getDaysLabel(),
                        'textSize'       => (int)(32 * 1.5),
                        'textPositionX'  => 50 * 2,
                        'textPositionY'  => 62 * 2 - 10,
                        'labelSize'      => 7 * 2,
                        'labelPositionX' => 52 * 2 - 9,
                        'labelPositionY' => 75 * 2 - 13,
                    ],
                    'hours'   => [
                        'label'          => $this->getHoursLabel(),
                        'textSize'       => (int)(30 * 1.5),
                        'textPositionX'  => 152 * 2 - 5,
                        'textPositionY'  => 62 * 2 - 10,
                        'labelSize'      => 7 * 2,
                        'labelPositionX' => 152 * 2 - 5,
                        'labelPositionY' => 75 * 2 - 10,
                    ],
                    'minutes' => [
                        'label'          => $this->getMinutesLabel(),
                        'textSize'       => (int)(30 * 1.5),
                        'textPositionX'  => 252 * 2 - 5,
                        'textPositionY'  => 62 * 2 - 10,
                        'labelSize'      => 7 * 2,
                        'labelPositionX' => 252 * 2 - 5,
                        'labelPositionY' => 75 * 2 - 10,
                    ],
                    'seconds' => [
                        'label'          => $this->getSecondsLabel(),
                        'textSize'       => (int)(30 * 1.5),
                        'textPositionX'  => 352 * 2 - 5,
                        'textPositionY'  => 62 * 2 - 10,
                        'labelSize'      => 7 * 2,
                        'labelPositionX' => 352 * 2 - 5,
                        'labelPositionY' => 75 * 2 - 10,
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function buildFrame($days, $hours, $minutes, $seconds)
    {
        if (!$this->showCircle) {
            return parent::buildFrame($days, $hours, $minutes, $seconds);
        }

        $frame = $this->createFrame();

        $circleImage = $this->getCircleImage($days, $hours, $minutes, $seconds);

        if (empty($circleImage)) {
            return parent::buildFrame($days, $hours, $minutes, $seconds);
        }
        // copy circle to
        // downsampling
        imagecopyresampled(
            $frame, // @phpstan-ignore-line
            $circleImage, // @phpstan-ignore-line
            0,
            0,
            0,
            0,
            $this->width,
            $this->height,
            (int)($this->width * $this->circleScale),
            (int)($this->height * $this->circleScale)
        );

        return $this->addText($frame, $days, $hours, $minutes, $seconds);
    }

    /**
     * adding texts for each frame
     *
     * @param resource $frame
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return resource
     */
    protected function addText($frame, $days, $hours, $minutes, $seconds)
    {
        $text_color = imagecolorallocate(
            $frame, // @phpstan-ignore-line
            $this->textColor['red'],
            $this->textColor['green'],
            $this->textColor['blue']
        );

        // calculate center of bounding box, so text is centered
        $daysBBox = (array)imagettfbbox($this->textData['days']['textSize'], 0, $this->fontFile, (string)round($days));
        $daysPositionX = $this->textData['days']['textPositionX'] - ($daysBBox[4] / 2);

        $hoursBBox = (array)imagettfbbox($this->textData['hours']['textSize'], 0, $this->fontFile, (string)round($hours));
        $hoursPositionX = $this->textData['hours']['textPositionX'] - ($hoursBBox[4] / 2);

        $minutesBBox = (array)imagettfbbox($this->textData['minutes']['textSize'], 0, $this->fontFile, (string)round($minutes));
        $minutesPositionX = $this->textData['minutes']['textPositionX'] - ($minutesBBox[4] / 2);

        $secondsBBox = (array)imagettfbbox($this->textData['seconds']['textSize'], 0, $this->fontFile, (string)round($seconds));
        $secondsPositionX = $this->textData['seconds']['textPositionX'] - ($secondsBBox[4] / 2);

        imagettftext(
            $frame, // @phpstan-ignore-line
            $this->textData['days']['textSize'],
            0,
            (int)round($daysPositionX),
            $this->textData['days']['textPositionY'],
            (int)$text_color,
            $this->fontFile,
            (string)round($days)
        );
        imagettftext(
            $frame, // @phpstan-ignore-line
            $this->textData['hours']['textSize'],
            0,
            (int)round($hoursPositionX),
            $this->textData['hours']['textPositionY'],
            (int)$text_color,
            $this->fontFile,
            (string)round($hours)
        );
        imagettftext(
            $frame, // @phpstan-ignore-line
            $this->textData['minutes']['textSize'],
            0,
            (int)round($minutesPositionX),
            $this->textData['minutes']['textPositionY'],
            (int)$text_color,
            $this->fontFile,
            (string)round($minutes)
        );
        imagettftext(
            $frame, // @phpstan-ignore-line
            $this->textData['seconds']['textSize'],
            0,
            (int)round($secondsPositionX),
            $this->textData['seconds']['textPositionY'],
            (int)$text_color,
            $this->fontFile,
            (string)round($seconds)
        );

        if ($this->showTextLabel) {
            $daysLabelBBox = (array)imagettfbbox($this->textData['days']['labelSize'], 0, $this->fontFile, $this->textData['days']['label']);
            $daysLabelPositionX = $this->textData['days']['labelPositionX'] - ($daysLabelBBox[4] / 2);

            $hoursLabelBBox = (array)imagettfbbox($this->textData['hours']['labelSize'], 0, $this->fontFile, $this->textData['hours']['label']);
            $hoursLabelPositionX = $this->textData['hours']['labelPositionX'] - ($hoursLabelBBox[4] / 2);

            $minutesLabelBBox = (array)imagettfbbox($this->textData['minutes']['labelSize'], 0, $this->fontFile, $this->textData['minutes']['label']);
            $minutesLabelPositionX = $this->textData['minutes']['labelPositionX'] - ($minutesLabelBBox[4] / 2);

            $secondsLabelBBox = (array)imagettfbbox($this->textData['seconds']['labelSize'], 0, $this->fontFile, $this->textData['seconds']['label']);
            $secondsLabelPositionX = $this->textData['seconds']['labelPositionX'] - ($secondsLabelBBox[4] / 2);

            imagettftext(
                $frame, // @phpstan-ignore-line
                $this->textData['days']['labelSize'],
                0,
                (int)round($daysLabelPositionX),
                $this->textData['days']['labelPositionY'],
                (int)$text_color,
                $this->fontFile,
                $this->textData['days']['label']
            );
            imagettftext(
                $frame, // @phpstan-ignore-line
                $this->textData['hours']['labelSize'],
                0,
                (int)round($hoursLabelPositionX),
                $this->textData['hours']['labelPositionY'],
                (int)$text_color,
                $this->fontFile,
                $this->textData['hours']['label']
            );
            imagettftext(
                $frame, // @phpstan-ignore-line
                $this->textData['minutes']['labelSize'],
                0,
                (int)round($minutesLabelPositionX),
                $this->textData['minutes']['labelPositionY'],
                (int)$text_color,
                $this->fontFile,
                $this->textData['minutes']['label']
            );
            imagettftext(
                $frame, // @phpstan-ignore-line
                $this->textData['seconds']['labelSize'],
                0,
                (int)round($secondsLabelPositionX),
                $this->textData['seconds']['labelPositionY'],
                (int)$text_color,
                $this->fontFile,
                $this->textData['seconds']['label']
            );
        }

        return $frame;
    }

    /**
     * get the circle image for the fake countdown
     *
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @return false|resource
     */
    private function getCircleImage(int $days, int $hours, int $minutes, int $seconds)
    {
        if (empty($this->circleImage)) {
            $circleImageWidth = (int)($this->width * $this->circleScale);
            $circleImageHeight = (int)($this->height * $this->circleScale);

            // create the circle image once
            $this->circleImage = imagecreatetruecolor($circleImageWidth, $circleImageHeight); // @phpstan-ignore-line

            if (empty($this->circleImage)) {
                return false;
            }
            // background
            $backgroundColor = (int)imagecolorallocate(
                $this->circleImage,
                $this->backgroundColor['red'],
                $this->backgroundColor['green'],
                $this->backgroundColor['blue']
            );
            imagefilledrectangle(
                $this->circleImage,
                0,
                0,
                $circleImageWidth,
                $circleImageHeight,
                $backgroundColor
            );

            imagesetthickness($this->circleImage, (int)($this->circleScale * 2));

            $this->circleBackgroundColorAll = (int)imagecolorallocate(
                $this->circleImage,
                $this->circleBackgroundColor['red'],
                $this->circleBackgroundColor['green'],
                $this->circleBackgroundColor['blue']
            );
            $this->circleForegroundColorAll = (int)imagecolorallocate(
                $this->circleImage,
                $this->circleForegroundColor['red'],
                $this->circleForegroundColor['green'],
                $this->circleForegroundColor['blue']
            );
        }

        $zoomWidth = (int)($this->circleWidth * $this->circleScale);
        $zoomHeight = (int)($this->circleHeight * $this->circleScale);

        // draw seconds circle
        imagearc(
            $this->circleImage, // @phpstan-ignore-line
            (int)((int)($zoomWidth / 2)) + 900,
            (int)($zoomHeight / 2),
            $zoomWidth - (int)(20 * $this->circleScale),
            $zoomHeight - (int)(20 * $this->circleScale),
            0,
            360,
            $this->circleBackgroundColorAll
        );
        imagearc(
            $this->circleImage, // @phpstan-ignore-line
            (int)((int)($zoomWidth / 2)) + 900,
            (int)($zoomHeight / 2),
            $zoomWidth - (int)(20 * $this->circleScale),
            $zoomHeight - (int)(20 * $this->circleScale),
            -90,
            -90 - (6 * $seconds),
            $this->circleForegroundColorAll
        );

        if (empty($this->lastMinutes) || $minutes != $this->lastMinutes) {
            imagearc(
                $this->circleImage, // @phpstan-ignore-line
                (int)((int)($zoomWidth / 2)) + 600,
                (int)($zoomHeight / 2),
                $zoomWidth - (int)(20 * $this->circleScale),
                $zoomHeight - (int)(20 * $this->circleScale),
                0,
                360,
                $this->circleBackgroundColorAll
            );
            imagearc(
                $this->circleImage, // @phpstan-ignore-line
                (int)((int)($zoomWidth / 2)) + 600,
                (int)($zoomHeight / 2),
                $zoomWidth - (int)(20 * $this->circleScale),
                $zoomHeight - (int)(20 * $this->circleScale),
                -90,
                -90 - (6 * $minutes),
                $this->circleForegroundColorAll
            );
            $this->lastMinutes = $minutes;
        }

        if (empty($this->lastHours) || $hours != $this->lastHours) {
            imagearc(
                $this->circleImage, // @phpstan-ignore-line
                (int)($zoomWidth / 2) + 300,
                (int)($zoomHeight / 2),
                $zoomWidth - (int)(20 * $this->circleScale),
                $zoomHeight - (int)(20 * $this->circleScale),
                0,
                360,
                $this->circleBackgroundColorAll
            );
            imagearc(
                $this->circleImage, // @phpstan-ignore-line
                (int)($zoomWidth / 2) + 300,
                (int)($zoomHeight / 2),
                $zoomWidth - (int)(20 * $this->circleScale),
                $zoomHeight - (int)(20 * $this->circleScale),
                -90,
                -90 - (15 * $hours),
                $this->circleForegroundColorAll
            );
            $this->lastHours = $hours;
        }

        if (empty($this->lastDays) || $days != $this->lastDays) {
            imagearc(
                $this->circleImage, // @phpstan-ignore-line
                (int)($zoomWidth / 2),
                (int)($zoomHeight / 2),
                $zoomWidth - (int)(20 * $this->circleScale),
                $zoomHeight - (int)(20 * $this->circleScale),
                0,
                360,
                $this->circleBackgroundColorAll
            );
            imagearc(
                $this->circleImage, // @phpstan-ignore-line
                (int)($zoomWidth / 2),
                (int)($zoomHeight / 2),
                $zoomWidth - (int)(20 * $this->circleScale),
                $zoomHeight - (int)(20 * $this->circleScale),
                -90,
                -90 - (1 * $days),
                $this->circleForegroundColorAll
            );
            $this->lastDays = $days;
        }

        return $this->circleImage; // @phpstan-ignore-line
    }
}
