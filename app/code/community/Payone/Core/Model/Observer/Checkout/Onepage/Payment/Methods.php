<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License (GPL 3)
 * that is bundled with this package in the file LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Payone_Core to newer
 * versions in the future. If you wish to customize Payone_Core for your
 * needs please refer to http://www.payone.de for more information.
 *
 * @category        Payone
 * @package         Payone_Core_Model
 * @subpackage      Observer
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @author          Matthias Walter <info@noovias.com>
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */

/**
 *
 * @category        Payone
 * @package         Payone_Core_Model
 * @subpackage      Observer
 * @copyright       Copyright (c) 2012 <info@noovias.com> - www.noovias.com
 * @license         <http://www.gnu.org/licenses/> GNU General Public License (GPL 3)
 * @link            http://www.noovias.com
 */
class Payone_Core_Model_Observer_Checkout_Onepage_Payment_Methods
    extends Payone_Core_Model_Observer_Abstract
{
    /**
     * @var Varien_Object
     */
    protected $settings = null;

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function getMethods(Varien_Event_Observer $observer)
    {
        $this->init($observer);
        /**
         * @var $quote Mage_Sales_Model_Quote
         */
        $quote = $observer->getEvent()->getQuote();

        /** @var $fullActionName string */
        $fullActionName = $observer->getEvent()->getFullActionName();
        if ($fullActionName === 'checkout/onepage/index') {
            return;
        }

        $config = $this->helperConfig()->getConfigProtect($quote->getStoreId())->getCreditrating();
        if (!$config->getEnabled()) {
            return;
        }

        // After Payment Select Checks will be run when action is verifyPayment
        if ($config->isIntegrationEventAfterPayment()) {
            // @comment we should never come into this observer using after_payment:
            // methods are determined using onepage_verify_payment event
            return;
        }

        if ($config->isIntegrationEventBeforePayment()) {
            $service = $this->getFactory()->getServiceVerificationCreditrating($config);
            $allowedMethods = $service->execute($quote);

            // Check not necessary
            if ($allowedMethods === true) {
                $this->setSettingsHavetoFilterMethods(false);
                return;
            }

            $this->setSettingsHavetoFilterMethods(true);
            $this->getSettingsAllowedMethods()->addData($allowedMethods);
        }
    }

    /**
     * @param int $value
     */
    protected function setSettingsHavetoFilterMethods($value)
    {
        $key = Payone_Core_Block_Checkout_Onepage_Payment_Methods::RESULT_HAVE_TO_FILTER_METHODS;
        $this->getSettings()->setData($key, $value);
    }

    /**
     * @return Varien_Object
     */
    protected function getSettingsAllowedMethods()
    {
        $key = Payone_Core_Block_Checkout_Onepage_Payment_Methods::RESULT_ALLOWED_METHODS;
        return $this->getSettings()->getData($key);
    }

    /**
     * @param Varien_Object $value
     * @return Varien_Object
     */
    protected function setSettingsAllowedMethods(Varien_Object $value)
    {
        $key = Payone_Core_Block_Checkout_Onepage_Payment_Methods::RESULT_ALLOWED_METHODS;
        return $this->getSettings()->setData($key, $value);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    protected function init(Varien_Event_Observer $observer)
    {
        $this->setSettings($observer->getEvent()->getSettings());
    }

    /**
     * @param Varien_Object $settings
     */
    public function setSettings(Varien_Object $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return Varien_Object
     */
    public function getSettings()
    {
        return $this->settings;
    }

}