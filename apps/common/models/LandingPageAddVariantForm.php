<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * LandingPageAddVariantForm
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.1.20
 */
class LandingPageAddVariantForm extends FormModel
{
    const CHOICE_DUPLICATE = 'duplicate';
    const CHOICE_SCRATCH = 'scratch';
    const CHOICE_TEMPLATE = 'template';

    /**
     * @var null|int
     */
    public $variant_id;

    /**
     * @var null|int
     */
    public $template_id;

    /**
     * @var string
     */
    public $choice = self::CHOICE_SCRATCH;

    /**
     * @var null|LandingPageRevisionVariant
     */
    private $_variant;

    /**
     * @var null|LandingPageTemplate
     */
    private $_template;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return CMap::mergeArray(parent::rules(), [
            ['choice', 'required'],
            ['choice', 'in', 'range' => [self::CHOICE_DUPLICATE, self::CHOICE_SCRATCH, self::CHOICE_TEMPLATE]],
            ['variant_id', 'required', 'on' => self::CHOICE_DUPLICATE],
            ['variant_id', 'exist', 'className' => LandingPageRevisionVariant::class],

            ['template_id', 'required', 'on' => self::CHOICE_TEMPLATE],
            ['template_id', 'exist', 'className' => LandingPageTemplate::class],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return CMap::mergeArray(parent::attributeLabels(), [
            'variant_id'  => t('landing_pages', 'Variant'),
            'template_id' => t('landing_pages', 'Template'),
        ]);
    }

    /**
     * @return LandingPageRevisionVariant|null
     */
    public function getSelectedVariant(): ?LandingPageRevisionVariant
    {
        if (!empty($this->variant_id)) {
            return $this->_variant = LandingPageRevisionVariant::model()->findByPk((int)$this->variant_id);
        }

        return null;
    }

    /**
     * @return LandingPageRevisionVariant|null
     */
    public function getVariant(): ?LandingPageRevisionVariant
    {
        if (!empty($this->_variant)) {
            return $this->_variant;
        }
        return $this->getSelectedVariant();
    }

    /**
     * @return LandingPageTemplate|null
     */
    public function getSelectedTemplate(): ?LandingPageTemplate
    {
        if (!empty($this->template_id)) {
            return $this->_template = LandingPageTemplate::model()->findByPk((int)$this->template_id);
        }

        return null;
    }

    /**
     * @return LandingPageTemplate|null
     */
    public function getTemplate(): ?LandingPageTemplate
    {
        if (!empty($this->_template)) {
            return $this->_template;
        }
        return $this->getSelectedTemplate();
    }

    /**
     * @return bool
     */
    public function getIsDuplicate(): bool
    {
        return $this->choice === self::CHOICE_DUPLICATE;
    }

    /**
     * @return bool
     */
    public function getIsScratch(): bool
    {
        return $this->choice === self::CHOICE_SCRATCH;
    }

    /**
     * @return bool
     */
    public function getIsTemplate(): bool
    {
        return $this->choice === self::CHOICE_TEMPLATE;
    }

    /**
     * @return bool
     */
    protected function beforeValidate(): bool
    {
        $this->setScenario($this->choice);

        if ($this->getIsScratch()) {
            $this->variant_id = $this->template_id = null;
        }

        return parent::beforeValidate();
    }
}
