<?php

namespace Lexik\Bundle\MonologBrowserBundle\Form;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Jeremy Barthe <j.barthe@lexik.fr>
 */
class LogSearchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('term', SearchType::class, [
                'required' => false,
            ]
            )
            ->add('level', ChoiceType::class, [
                'choices'     => $options['log_levels'],
                'required'    => false,
            ]
            )
            ->add('date_from', DateTimeType::class, [
                'date_widget' => 'single_text',
                'date_format' => 'MM/dd/yyyy',
                'time_widget' => 'text',
                'required'    => false,
            ]
            )
            ->add('date_to', DateTimeType::class, [
                'date_widget' => 'single_text',
                'date_format' => 'MM/dd/yyyy',
                'time_widget' => 'text',
                'required'    => false,
            ]
            )
        ;

        $qb = $options['query_builder'];
        $convertDateToDatabaseValue = function(\DateTime $date) use ($qb) {
            return Type::getType('datetime')->convertToDatabaseValue($date, $qb->getConnection()->getDatabasePlatform());
        };

        $builder->addEventListener(FormEvents::POST_BIND, function(FormEvent $event) use ($qb, $convertDateToDatabaseValue) {
            $data = $event->getData();

            if (null !== $data['term']) {
                $qb->andWhere('l.message LIKE :message')
                   ->setParameter('message', '%'.str_replace(' ', '%', $data['term']).'%')
                   ->orWhere('l.channel LIKE :channel')
                   ->setParameter('channel', $data['term'].'%');
            }

            if (null !== $data['level']) {
                $qb->andWhere('l.level = :level')
                   ->setParameter('level', $data['level']);
            }

            if ($data['date_from'] instanceof \DateTime) {
                $qb->andWhere('l.datetime >= :date_from')
                   ->setParameter('date_from', $convertDateToDatabaseValue($data['date_from']));
            }

            if ($data['date_to'] instanceof \DateTime) {
                $qb->andWhere('l.datetime <= :date_to')
                   ->setParameter('date_to', $convertDateToDatabaseValue($data['date_to']));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(
                [
                'query_builder',
                ]
            )
            ->setDefaults(
                [
                'log_levels'      => [],
                'csrf_protection' => false,
                ]
            )
            ->setAllowedTypes(
                'log_levels',
                'array'
            )
            ->setAllowedTypes(
                'query_builder',
                QueryBuilder::class
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'search';
    }
}
