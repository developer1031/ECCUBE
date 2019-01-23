<?php

namespace Plugin\Efo\Repository;

use Doctrine\ORM\EntityRepository;
use Plugin\Efo\Entity\EntryForm;

class EntryFormRepository extends EntityRepository
{
    /**
     * @param \Plugin\Efo\Entity\EntryForm $EntryForm
     */
    public function save(EntryForm $EntryForm)
    {
        $this->getEntityManager()->persist($EntryForm->getPageLayout());
        $this->getEntityManager()->flush($EntryForm->getPageLayout());

        $this->getEntityManager()->persist($EntryForm);
        $this->getEntityManager()->flush($EntryForm);
    }

    /**
     * @param \Plugin\Efo\Entity\EntryForm $EntryForm
     */
    public function delete(EntryForm $EntryForm)
    {
        $this->getEntityManager()->remove($EntryForm);
        $this->getEntityManager()->flush($EntryForm);
    }

    public function createAdminSearchQueryBuilder(array $conditions = array())
    {
        $builder = $this->createQueryBuilder('entry_form');

        $products = $this->getEntityManager()
            ->createQueryBuilder()
            ->from('Eccube\\Entity\\Product', 'product')
            ->andWhere('product.id = entry_form.product_id')
            ->select('product');

        $builder
            ->andWhere($builder->expr()->exists($products));

        if (isset($conditions['keyword']) && $conditions['keyword'] != '') {
            $builder
                ->andWhere('entry_form.id = :id')
                ->setParameter(':id', $conditions['keyword']);
        }

        if (isset($conditions['create_date_start'])) {
            /** @var \DateTime $start */
            $start = $conditions['create_date_start'];
            $start->setTime(0, 0, 0);
            $builder
                ->andWhere('entry_form.create_date >= :create_date_start')
                ->setParameter('create_date_start', $start->format('Y-m-d H:i:s'));
        }

        if (isset($conditions['create_date_end'])) {
            /** @var \DateTime $end */
            $end = $conditions['create_date_end'];
            $end->setTime(0, 0, 0);
            $end->add((new \DateInterval('P1D')));
            $builder
                ->andWhere('entry_form.create_date < :create_date_end')
                ->setParameter('create_date_end', $end->format('Y-m-d H:i:s'));
        }

        if (isset($conditions['update_date_start'])) {
            /** @var \DateTime $start */
            $start = $conditions['update_date_start'];
            $start->setTime(0, 0, 0);
            $builder
                ->andWhere('entry_form.update_date >= :update_date_start')
                ->setParameter('update_date_start', $start->format('Y-m-d H:i:s'));
        }

        if (isset($conditions['update_date_end'])) {
            /** @var \DateTime $end */
            $end = $conditions['update_date_end'];
            $end->setTime(0, 0, 0);
            $end->add((new \DateInterval('P1D')));
            $builder
                ->andWhere('entry_form.update_date < :update_date_end')
                ->setParameter('update_date_end', $end->format('Y-m-d H:i:s'));
        }

        $builder->orderBy('entry_form.create_date', 'DESC');

        return $builder;
    }

    /**
     * @return \Plugin\Efo\Entity\EntryForm[]
     */
    public function findByActive()
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->from('\\Plugin\\Efo\\Entity\\EntryForm', 'entry_form')
            ->select('entry_form')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $path
     * @return \Plugin\Efo\Entity\EntryForm|null
     */
    public function findOneByPath($path)
    {
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->from('\\Plugin\\Efo\\Entity\\EntryForm', 'entry_form')
            ->select('entry_form')
            ->andWhere('entry_form.path = :path')
            ->setParameter(':path', $path)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
