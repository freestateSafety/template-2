<?php
namespace App\Form\Type;


use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Product Detail'])
            ->add('category', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, ['class' => \App\Entity\ProductCategory::class, 'query_builder' => fn(EntityRepository $repository) => $repository->createQueryBuilder('c')->where('c.parent IS NULL')->orderBy('c.priority')])
            ->add('material', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, ['class' => \App\Entity\Material::class])
            ->add('itemNumber', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [])
            ->add('size', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [])
            ->add('shape', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false])
            ->add('weight', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [])
            ->add('quantity', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false])
            ->add('class', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['required' => false])
            ->add('notes', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, ['required' => false])
            ->add('image', \Symfony\Component\Form\Extension\Core\Type\FileType::class, ['property_path' => 'image', 'required' => true]);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                /** @var Product $product */
                $product = $event->getData();
                $form->add('productCategory', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, ['class' => \App\Entity\ProductCategory::class, 'query_builder' => fn(EntityRepository $repository) => $repository->createQueryBuilder('c')->where('c.parent = ?1')->setParameter(1, $product->getCategory())->orderBy('c.priority')]);

                if ($product && $product->getId() !== null) {
                    $field = $form->get('image');
                    $options = $field->getConfig()->getOptions();
                    $type = $field->getConfig()->getType()->getInnerType();
                    $options['required'] = false;
                    $form->add('image', $type::class, $options);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Product::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'product';
    }
}
