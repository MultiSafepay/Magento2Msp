<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MultiSafepay\Connect\Model\Observers;

use MultiSafepay\Connect\Block\Fastcheckout\Button;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddFastcheckoutShortcuts implements ObserverInterface
{

    /**
     * Block class
     */
    const FASTCHECKOUT_SHORTCUT_BLOCK = Button::class;

    public function execute(Observer $observer)
    {

        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        $shortcut = $shortcutButtons->getLayout()->createBlock(self::FASTCHECKOUT_SHORTCUT_BLOCK);

        $shortcutButtons->addShortcut($shortcut);
    }

}
