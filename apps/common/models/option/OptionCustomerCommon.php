<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * OptionCustomerCommon
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
 */

class OptionCustomerCommon extends OptionBase
{
    public const UNREAD_MESSAGES_REMINDER_FREQUENCY_OFF = 0;
    public const UNREAD_MESSAGES_REMINDER_FREQUENCY_DAILY = 1;
    public const UNREAD_MESSAGES_REMINDER_FREQUENCY_WEEKLY = 2;
    public const UNREAD_MESSAGES_REMINDER_FREQUENCY_MONTHLY = 3;

    /**
     * @var string
     */
    public $notification_message;

    /**
     * @var string
     */
    public $show_articles_menu = self::TEXT_NO;

    /**
     * @var string
     */
    public $mask_email_addresses = self::TEXT_NO;

    /**
     * @var int
     */
    public $days_to_keep_disabled_account = 30;

    /**
     * @var int
     */
    public $unread_messages_reminder_frequency = self::UNREAD_MESSAGES_REMINDER_FREQUENCY_DAILY;

    /**
     * @var string
     */
    protected $_categoryName = 'system.customer_common';

    /**
     * @return array
     * @throws CException
     */
    public function rules()
    {
        $rules = [
            ['notification_message', 'safe'],
            ['show_articles_menu, mask_email_addresses', 'in', 'range' => array_keys($this->getYesNoOptions())],
            ['days_to_keep_disabled_account', 'numerical', 'integerOnly' => true, 'min' => -1, 'max' => 365],
            ['unread_messages_reminder_frequency', 'in', 'range' => array_keys($this->getUnreadMessagesReminderFrequencyOptions())],
        ];

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributeLabels()
    {
        $labels = [
            'notification_message'                  => $this->t('Notification message'),
            'show_articles_menu'                    => $this->t('Show articles menu'),
            'mask_email_addresses'                  => $this->t('Mask email addresses'),
            'days_to_keep_disabled_account'         => $this->t('Days to keep disabled account'),
            'unread_messages_reminder_frequency'    => $this->t('Unread messages reminder frequency'),
        ];

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributePlaceholders()
    {
        $placeholders = [
            'notification_message'  => '',
            'show_articles_menu'    => '',
            'mask_email_addresses'  => '',
            'days_to_keep_disabled_account' => 30,
        ];

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @return array
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $texts = [
            'notification_message'                  => $this->t('A small persistent notification message shown in customers area'),
            'show_articles_menu'                    => $this->t('Whether to show the articles link in the menu'),
            'mask_email_addresses'                  => $this->t('Whether to mask the email addresses, i.e: abcdef@gmail.com becomes a****f@gmail.com'),
            'days_to_keep_disabled_account'         => $this->t('If the customer disables their account, how many days we should keep it in the system until we remove it for good. Set to -1 for unlimited'),
            'unread_messages_reminder_frequency'    => $this->t('How often should the customer be notified they have unread messages. The notification will be sent only if there are unread messages'),
        ];

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return int
     */
    public function getDaysToKeepDisabledAccount(): int
    {
        return (int)$this->days_to_keep_disabled_account;
    }

    /**
     * @return array
     */
    public function getUnreadMessagesReminderFrequencyOptions(): array
    {
        return [
            self::UNREAD_MESSAGES_REMINDER_FREQUENCY_OFF        => $this->t('Disabled'),
            self::UNREAD_MESSAGES_REMINDER_FREQUENCY_DAILY      => $this->t('Daily'),
            self::UNREAD_MESSAGES_REMINDER_FREQUENCY_WEEKLY     => $this->t('Weekly'),
            self::UNREAD_MESSAGES_REMINDER_FREQUENCY_MONTHLY    => $this->t('Monthly'),
        ];
    }
}
