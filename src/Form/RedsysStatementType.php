<?php

/*
 * This file is part of the Redsys to OFX package.
 *
 * (c) Gorka Maiztegi <gmaiztegi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\RedsysStatement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RedsysStatementType
 *
 * @author Gorka Maiztegi <gmaiztegi@gmail.com>
 */
class RedsysStatementType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('commerceId', null, array(
                'label' => "Commerce id",
            ))
            ->add('consignmentStatement', FileType::class, array(
                'label' => 'Consignment Statement (.xls)',
            ))
            ->add('transactionStatement', FileType::class, array(
                'label' => 'Redsys transactions (.csv)',
            ))
            ->add('submit', SubmitType::class, array(
                'attr' => array('class' => 'btn-primary'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => RedsysStatement::class,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mniredsys_to_ofx_bundle_redsys_statement_type';
    }
}
