<?php
declare(strict_types=1);

namespace Codilar\PrivateOverride\Block\Account;

use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Html\Links;

/**
 * Copy of Magento\Customer\Block\Account\Navigation (the My Account side menu).
 *
 * Why a copy instead of "extends Navigation": the sorting rule is in the PRIVATE method compare().
 * A subclass cannot override a private method, and the vendor getLinks() would keep calling its
 * own. Copying the class makes compare() ours, so we can change it.
 *
 * Only compare() is changed; everything else is identical to the vendor class.
 * Maintenance note: this copy does not follow vendor updates to Navigation, so review it
 * after a Magento upgrade.
 */
class Navigation extends Links
{
    /**
     * Same as the vendor method: sortable links are sorted with compare(), the rest are appended.
     *
     * @inheritdoc
     */
    public function getLinks()
    {
        $links = $this->_layout->getChildBlocks($this->getNameInLayout());
        $sortableLink = [];
        foreach ($links as $key => $link) {
            if ($link instanceof SortLinkInterface) {
                $sortableLink[] = $link;
                unset($links[$key]);
            }
        }

        usort($sortableLink, [$this, "compare"]);
        return array_merge($sortableLink, $links);
    }

    /**
     * CHANGED. Vendor version sorts by sortOrder, highest first:
     *     return $secondLink->getSortOrder() <=> $firstLink->getSortOrder();
     * This version sorts the menu alphabetically by label (A to Z).
     *
     * @param SortLinkInterface $firstLink
     * @param SortLinkInterface $secondLink
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function compare(SortLinkInterface $firstLink, SortLinkInterface $secondLink): int
    {
        return strcasecmp($this->getLabelOf($firstLink), $this->getLabelOf($secondLink));
    }

    /**
     * @param SortLinkInterface $link`
     * @return string
     */
    private function getLabelOf(SortLinkInterface $link): string
    {
        // Menu links are layout blocks (DataObjects); the visible text is in their "label" data.
        return $link instanceof DataObject ? (string)__($link->getData('label')) : '';
    }
}
