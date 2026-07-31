<?php
declare(strict_types=1);

namespace User\MboPasswordReset\Observer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\User;
use Psr\Log\LoggerInterface;

/**
 * Listens to admin_user_save_after.
 *
 * When a brand-new admin user is saved with user_type = 'mbo',
 * force_password_change is set to 1 via a direct DB write to avoid
 * triggering the model save cycle again.
 */
class SetForcePasswordChangeOnCreate implements ObserverInterface
{
    private ResourceConnection $resourceConnection;
    private LoggerInterface $logger;

    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger             = $logger;
    }

    public function execute(Observer $observer): void
    {
        /** @var User $user */
        $user = $observer->getEvent()->getObject();

        if (!$user instanceof User) {
            return;
        }

        // Only act on brand-new accounts.
        if (!$user->isObjectNew()) {
            return;
        }

        try {
            $connection = $this->resourceConnection->getConnection();
            $table      = $this->resourceConnection->getTableName('admin_user');

            $connection->update(
                $table,
                ['force_password_change' => 1],
                ['user_id = ?' => (int)$user->getId()]
            );
        } catch (\Exception $e) {
            $this->logger->error(
                'MboPasswordReset: failed to set force_password_change for user ' . $user->getId(),
                ['exception' => $e]
            );
        }
    }
}
