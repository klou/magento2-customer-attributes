<?php

/**
 * Copyright © 2019 Mvn. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tangkoko\CustomerAttributesManagement\Block\Form;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class Attributes
 * @package Tangkoko\CustomerAttributesManagement
 * @method \Tangkoko\CustomerAttributesManagement\ViewModel\Form\Attributes getViewModel()
 */
class Attributes extends \Magento\Framework\View\Element\Template
{


    protected AttributeFactory $attributeFactory;

    protected SerializerInterface $serializer;


    protected $fieldsetAttributes = [];

    /**
     * Constructor
     *
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        AttributeFactory $attributeFactory,
        SerializerInterface $serializer,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->attributeFactory =  $attributeFactory;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        if ($this->getFormCode()) {
            $this->createChildBlocks();
        }

        return parent::_prepareLayout();
    }

    protected function createChildBlocks()
    {
        foreach ($this->getViewModel()->getAttributes($this->getFormCode()) as $attributeCode => $attribute) {
            $block = null;
            $fields = $this->getFields();
            if ($attribute->getIsUserDefined() || (is_array($fields) && in_array($attribute->getAttributeCode(), array_keys($fields)) && $fields[$attribute->getAttributeCode()])) {
                $block = $this->attributeFactory->create($attribute, $this->getFormData());
                if ($attributeViewModel = $this->getViewModel()->getAttributeViewModel()) {
                    $block->setViewModel($attributeViewModel);
                }
                $this->fieldsetAttributes[$attribute->getExtensionAttributes()->getCamAttribute()->getFieldset()][] = $attribute;
            }
            if ($block) {
                $this->setChild($this->getNameInLayout() . '.' .  $attribute->getAttributeCode(), $block);
            }
        }
    }


    /**
     * return field
     *
     * @param string $fieldsetName
     * @return AttributeInterface[]
     */
    public function getAttributes(string $fieldsetName)
    {
        if (isset($this->fieldsetAttributes[$fieldsetName])) {
            return $this->fieldsetAttributes[$fieldsetName];
        }
        return [];
    }




    /**
     * Retrieve form data
     *
     * @return mixed
     */
    public function getFormData(): DataObject
    {
        $data = $this->getData('form_data');
        if ($data === null) {
            $data = $this->getViewModel()->getFormData();
            $this->setData('form_data', $data);
        }
        return $data;
    }

    /**
     * Return attribute configuration json encoded
     *
     * @return string
     */
    public function getJsonConfig()
    {
        return $this->serializer->serialize(
            ["conditions" => $this->getViewModel()->getRules($this->getFormCode()), "model" => $this->getViewModel()->getFormData()->toArray()]
        );
    }
}