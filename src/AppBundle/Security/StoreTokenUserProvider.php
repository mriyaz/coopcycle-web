<?php

namespace AppBundle\Security;

use AppBundle\Entity\Store;
use Doctrine\ORM\Query\Expr;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Security\UserProvider as BaseUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class StoreTokenUserProvider extends BaseUserProvider
{
    private $repository;

    public function __construct(UserManagerInterface $userManager, $doctrine)
    {
        parent::__construct($userManager);

        $this->repository = $doctrine->getRepository($userManager->getClass());
    }

    public function loadUserByUsername($username)
    {
        $qb = $this->repository
            ->createQueryBuilder('user')
            ->join('user.stores', 'user_stores')
            ->andWhere('user_stores.id = :store')
            ->setParameter('store', $username->getStore());

        $users = $qb->getQuery()->getResult();

        if (count($users) === 0) {
            return;
        }

        return current($users);
    }
}
