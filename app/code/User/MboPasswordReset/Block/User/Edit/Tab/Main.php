<?php
declare(strict_types=1);

namespace User\MboPasswordReset\Block\User\Edit\Tab;

/**
 * Extends the core admin user form to add a User Type selector.
 *
 * Declared as a preference in etc/adminhtml/di.xml.
 * The field is added before setValues() so the stored value is populated automatically.
 */
class Main extends \Magento\User\Block\User\Edit\Tab\Main
{
    protected function _prepareForm(): self
    {
        // Let the parent build the full form first.
        parent::_prepareForm();

        $form     = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');

        // Guard against double-injection (e.g. layout called twice).
        if ($fieldset && !$form->getElement('user_type')) {
            $fieldset->addField(
                'user_type',
                'select',
                [
                    'name'   => 'user_type',
                    'label'  => __('User Type'),
                    'title'  => __('User Type'),
                    'values' => [
                        ['value' => '',      'label' => __('-- Select Type --')],
                        ['value' => 'admin', 'label' => __('Admin')],
                        ['value' => 'mbo',   'label' => __('MBO')],
                    ],
                ]
            );

            // Re-apply only the user_type value because setValues() already ran in parent.
            $model = $this->_coreRegistry->registry('permissions_user');
            if ($model) {
                $form->getElement('user_type')->setValue((string)$model->getData('user_type'));
            }
        }

        return $this;
    }
}
