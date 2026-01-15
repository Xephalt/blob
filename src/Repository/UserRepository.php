<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\Common\Period\Period;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\ArrayParameterType;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param int[]|null $weekdays 1 (Monday) -> 7 (Sunday)
     */
    public function countRegisteredBetween(Period $period, ?array $weekdays): int
    {
        $sql = <<<SQL
            SELECT COUNT(u.id)
            FROM user u
            WHERE u.created_at BETWEEN :from AND :to
        SQL;

        if ($weekdays !== null) {
            $sql .= ' AND DAYOFWEEK(u.created_at) IN (:weekdays)';
        }

        $params = [
            'from' => $period->from()->format('Y-m-d H:i:s'),
            'to'   => $period->to()->format('Y-m-d H:i:s'),
        ];

        if ($weekdays !== null) {
            // mapping métier (1=lundi..7=dimanche) -> MySQL (1=dimanche..7=samedi)
            $params['weekdays'] = array_map(
                static fn (int $d): int => $d === 7 ? 1 : $d + 1,
                $weekdays
            );
        }

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->executeQuery(
            $sql,
            $params,
            $weekdays !== null
                ? ['weekdays' => ArrayParameterType::INTEGER]
                : []
        );

        return (int) $stmt->fetchOne();
    }
}
