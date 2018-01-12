<?php
namespace AppBundle\Models;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Model that will take care of registration
 *
 * @author chippison
 *         Date created: 24 Nov 2017
 *
 */
class RegistrationForm extends AbstractType
{

    //private $builder;

//     function __construct(FormBuilderInterface $builder, array $opts)
//     {
//         $this->builder = $builder;
//     }

    //public function createRegistrationForm()
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $schools = $options['schools'];
        //$form = $this->builder->add('fname', TextType::class, array(
        $builder->add('fname', TextType::class, array(
                'label' => 'Principal First Name*',
                'attr' => array(
                    'placeholder' => 'Principal First Name*',
                    'class' => 'form-control'
                )
                ))
            ->add('lname', TextType::class, array(
                'label' => 'Principal Last Name*',
                'attr' => array(
                    'placeholder' => 'Principal Last Name*',
                    'class' => 'form-control'
                )
            ))
            ->add('email', RepeatedType::class, array(
                'type' => EmailType::class,
                'invalid_message' => 'Principal email fields must match',
                'first_options' => array(
                    'label' => 'Email*',
                    'constraints' => array(
                        new Email(array(
                            'message' => 'Incorrect Email Format'
                        ))
                    ),
                    'attr' => array(
                        'placeholder' => 'Email*',
                        'class' => 'form-control'
                    )
                ),
                'second_options' => array(
                    'label' => 'Confirm Email*',
                    'attr' => array(
                        'placeholder' => 'Confirm Email*',
                        'class' => 'form-control'
                    )
                )
            ))
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Password fields must match',
                'first_options' => array(
                    'label' => 'Password*',
                    'attr' => array(
                        'placeholder' => 'Password*',
                        'class' => 'form-control'
                    ),
                    'constraints' => array(
                        new Length(array(
                            'min' => 8
                        )),
                        new Regex(array(
                            'pattern' => "/\d/",
                            'message' => 'Password must contain a number'
                        )),
                        new Regex(array(
                            'pattern' => "/[A-Z]/",
                            'message' => 'Password must contain at least 1 capital letter'
                        ))
                    )
                ),
                'second_options' => array(
                    'label' => 'Confirm Password',
                    'attr' => array(
                        'placeholder' => 'Confirm Password*',
                        'class' => 'form-control'
                    )
                )
            ))

        // ->add('moe',TextType::class,array('label'=>'School MOE Number','attr'=>array(
        // 'placeholder'=>'School MOE Number*','class'=>'form-control')))
            ->add('moe', ChoiceType::class, array(
                'label' => 'School MOE Number',
                'attr' => array(
                    'placeholder' => 'School MOE Number*',
                    'class' => 'selectpicker',
                    'data-live-search' => 'true',
                    'data-live-search-style' => 'contains',
                    'data-title' => 'Search School Name or MOE Number',
                    'data-width' => '100%',
                    'data-dropup-auto' => 'false'
                ),
                'choices' => $schools,
                //'choices'=>array(),
                'choice_attr' => function ($val, $key, $index) {
                    return [
                        'data-tokens' => $val . ' ' . $key
                    ];
                }

            ))
            ->add('isadmin', ChoiceType::class, array(
                'choices' => array(
                    'Principal will be the administrator' => 1,
                    'I nominate another person to be administrator' => 0
                ),
                'choice_attr' => function ($val, $key, $index) {
                    // adds a class like attending_yes, attending_no, etc
                    return [
                        'data-toggle' => 'radio',
                        'class' => 'is_admin_radio'
                    ];
                },
                'multiple' => false,
                'expanded' => true,
                'required' => true,
                'data' => true
            ))
            ->add('cols', ChoiceType::class, array(
                'choices' => array(
                    'I allow myself to be included in the CoL Reporting' => 1
                ),
                'choice_attr' => function ($val, $key, $index) {
                    // adds a class like attending_yes, attending_no, etc
                    return [
                        'data-toggle' => 'checkbox',
                        'class' => 'col_chkbox'
                    ];
                },
                'multiple' => true,
                'expanded' => true,
                'required' => true
            ))
            ->add('terms', ChoiceType::class, array(
                'choices' => array(
                    'I have read and understood the terms and conditions' => 1
                ),
                'choice_attr' => function ($val, $key, $index) {
                    // adds a class like attending_yes, attending_no, etc
                    return [
                        'data-toggle' => 'checkbox',
                        'class' => 'terms_chkbox'
                    ];
                },
                'multiple' => true,
                'expanded' => true,
                'required' => true
            ))
            ->add('a_fname', TextType::class, array(
                'label' => 'Administrator First Name*',
                'attr' => array(
                    'placeholder' => 'Administrator First Name*',
                    'class' => 'form-control'
                )
            ))
            ->add('a_lname', TextType::class, array(
                'label' => 'Administrator Last Name*',
                'attr' => array(
                    'placeholder' => 'Administration Last Name*',
                    'class' => 'form-control'
                )
            ))
            ->add('a_email', RepeatedType::class, array(
                'type' => TextType::class,
                'invalid_message' => 'Administrator email fields must match',
                'first_options' => array(
                    'label' => 'Administrator Email*',
                    'attr' => array(
                        'placeholder' => 'Administrator Email*',
                        'class' => 'form-control'
                    )
                ),
                'second_options' => array(
                    'label' => 'Confirm administrator Email*',
                    'attr' => array(
                        'placeholder' => 'Confirm administrator email*',
                        'class' => 'form-control'
                    )
                )
            ))
            ->add('a_password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Password fields must match',
                'first_options' => array(
                    'label' => 'Password*',
                    'attr' => array(
                        'placeholder' => 'Password*',
                        'class' => 'form-control'
                    ),
                    'constraints' => array(
                        new Length(array(
                            'min' => 8
                        )),
                        new Regex(array(
                            'pattern' => "/\d/",
                            'message' => 'Password must contain a number'
                        )),
                        new Regex(array(
                            'pattern' => "/[A-Z]/",
                            'message' => 'Password must contain at least 1 capital letter'
                        ))
                    )
                ),
                'second_options' => array(
                    'label' => 'Confirm Password',
                    'attr' => array(
                        'placeholder' => 'Confirm Password*',
                        'class' => 'form-control'
                    )
                )
            ))
            ->add('register', SubmitType::class, array(
                'label' => 'Register',
                'attr' => array(
                    'class' => 'btn btn-danger btn-block'
                ),
                'disabled' => true
            ));
            //->getForm();
            //return $form;
    }
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setRequired('schools');
    }
}