<?php

namespace App\Components;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Service\UploadService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('registration_form')]
class RegistrationForm extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;
    #[LiveProp]
    public bool $isSuccessful = false;

    #[LiveProp]
    public ?string $newUserEmail = null;

    #[LiveProp(writable: true)]
    public ?string $email = null;

    private User $user;

    public function __construct() {
        $this->user = new User();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(RegistrationFormType::class, $this->user);
    }

    public function hasValidationErrors(): bool
    {
        return $this->getForm()->isSubmitted() && !$this->getForm()->isValid();
    }

    #[LiveAction]
    public function validateEmailUniqueness(UserRepository $userRepository): void
    {
        $this->isSuccessful = true;
        $email = $this->getForm()->get('email')->getData();
        $existingUser = $userRepository->findOneBy(['email' => $email]);

        if ($existingUser) {
            $this->getForm()->get('email')->addError(new FormError('Cet email est déjà utilisé.'));
        }
    }

    #[LiveAction]
    public function saveRegistration(UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UploadService $uploadService)
    {
        $this->submitForm();

        // save to the database
        // or, instead of creating a LiveAction, allow the form to submit
        // to a normal controller: that's even better.
        // $newUser = $this->getFormInstance()->getData();

        $this->user->setPassword(
            $userPasswordHasher->hashPassword(
                $this->user,
                $this->getForm()->get('password')->getData()
            )
        );

        $this->user->setCreatedAt(new DateTime());
        $this->user->setUpdatedAt(new DateTime());

        // Upload de l'avatar
        // Récupère les données du fichier
        /** @var UploadedFile $avatarFile */
        $profile_picture = $this->getForm()->get('profile_picture')->getData();

        // Si existant, on upload
        if ($profile_picture) {
            $newFilename = $uploadService->upload($profile_picture);
            $this->user->setProfilePicture($newFilename);
        }

        $entityManager->persist($this->user);
        $entityManager->flush();

        $this->newUserEmail = $this->getForm()
            ->get('email')
            ->getData();
        $this->isSuccessful = true;
    }
}