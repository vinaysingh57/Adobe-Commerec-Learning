<?php
namespace Cron\Learning\Cron;

use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User\CollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class DeactivateMboUser
{
    protected $userFactory;
    protected $userCollectionFactory;
    protected $dateTime;
    protected $state;

    public function __construct(
        UserFactory $userFactory,
        CollectionFactory $userCollectionFactory,
        DateTime $dateTime,
        State $state
    ) {
        $this->userFactory = $userFactory;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->dateTime = $dateTime;
        $this->state = $state;
    }

    public function execute()
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $now = $this->dateTime->gmtTimestamp();
        $days90 = 90 * 24 * 60 * 60;
        $threshold = $now - $days90;

        $collection = $this->userCollectionFactory->create();
        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter('user_type', 'mbo'); // Adjust field name if needed
        $collection->addFieldToFilter('last_login', ['lt' => date('Y-m-d H:i:s', $threshold)]);

        foreach ($collection as $user) {
            $user->setIsActive(0);
            $user->save();
        }
    }
}
