<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Google2FAService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/2fa')]
#[IsGranted('ROLE_USER')]
class Google2FAController extends AbstractController
{
    public function __construct(
        private Google2FAService $google2FAService,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/enable', name: 'google2fa_enable', methods: ['POST'])]
    public function enable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        // Générer un nouveau secret si l'utilisateur n'en a pas
        $secret = $user->getGoogle2faSecret();
        if (!$secret) {
            $secret = $this->google2FAService->generateSecret();
            $user->setGoogle2faSecret($secret);
            $this->entityManager->flush();
        }

        // Générer l'URL du QR code
        $qrCodeUrl = $this->google2FAService->getQRCodeUrl(
            $user->getEmail(),
            $secret,
            'WR506D'
        );

        return new JsonResponse([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    #[Route('/verify-enable', name: 'google2fa_verify_enable', methods: ['POST'])]
    public function verifyEnable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $otp = $data['otp'] ?? null;

        if (!$otp) {
            return new JsonResponse(['error' => 'OTP code is required'], Response::HTTP_BAD_REQUEST);
        }

        $secret = $user->getGoogle2faSecret();
        if (!$secret) {
            return new JsonResponse(
                ['error' => '2FA secret not found. Please enable 2FA first.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Vérifier le code
        if (!$this->google2FAService->verifyCode($secret, $otp)) {
            return new JsonResponse(['error' => 'Invalid OTP code'], Response::HTTP_BAD_REQUEST);
        }

        // Activer la 2FA
        $user->setGoogle2faEnabled(true);
        
        // Générer des codes de récupération
        $recoveryCodes = $this->google2FAService->generateRecoveryCodes();
        $user->setGoogle2faRecoveryCodes($recoveryCodes);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => '2FA enabled successfully',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    #[Route('/verify', name: 'google2fa_verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $otp = $data['otp'] ?? null;
        $recoveryCode = $data['recovery_code'] ?? null;

        if (!$otp && !$recoveryCode) {
            return new JsonResponse(['error' => 'OTP code or recovery code is required'], Response::HTTP_BAD_REQUEST);
        }

        $secret = $user->getGoogle2faSecret();
        if (!$secret) {
            return new JsonResponse(['error' => '2FA is not enabled'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier le code OTP
        if ($otp && $this->google2FAService->verifyCode($secret, $otp)) {
            return new JsonResponse(['message' => '2FA verification successful']);
        }

        // Vérifier le code de récupération
        if ($recoveryCode) {
            $recoveryCodes = $user->getGoogle2faRecoveryCodes() ?? [];
            if (in_array($recoveryCode, $recoveryCodes, true)) {
                // Supprimer le code de récupération utilisé
                $recoveryCodes = array_values(array_filter($recoveryCodes, fn($code) => $code !== $recoveryCode));
                $user->setGoogle2faRecoveryCodes($recoveryCodes);
                $this->entityManager->flush();

                return new JsonResponse(['message' => '2FA verification successful with recovery code']);
            }
        }

        return new JsonResponse(['error' => 'Invalid OTP code or recovery code'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/disable', name: 'google2fa_disable', methods: ['POST'])]
    public function disable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $otp = $data['otp'] ?? null;

        if (!$otp) {
            return new JsonResponse(['error' => 'OTP code is required to disable 2FA'], Response::HTTP_BAD_REQUEST);
        }

        $secret = $user->getGoogle2faSecret();
        if (!$secret) {
            return new JsonResponse(['error' => '2FA is not enabled'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier le code avant de désactiver
        if (!$this->google2FAService->verifyCode($secret, $otp)) {
            return new JsonResponse(['error' => 'Invalid OTP code'], Response::HTTP_BAD_REQUEST);
        }

        // Désactiver la 2FA
        $user->setGoogle2faEnabled(false);
        $user->setGoogle2faSecret(null);
        $user->setGoogle2faRecoveryCodes(null);

        $this->entityManager->flush();

        return new JsonResponse(['message' => '2FA disabled successfully']);
    }

    #[Route('/recovery-codes', name: 'google2fa_recovery_codes', methods: ['POST'])]
    public function generateRecoveryCodes(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isGoogle2faEnabled()) {
            return new JsonResponse(['error' => '2FA is not enabled'], Response::HTTP_BAD_REQUEST);
        }

        // Générer de nouveaux codes de récupération
        $recoveryCodes = $this->google2FAService->generateRecoveryCodes();
        $user->setGoogle2faRecoveryCodes($recoveryCodes);

        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Recovery codes generated successfully',
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    #[Route('/status', name: 'google2fa_status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'enabled' => $user->isGoogle2faEnabled(),
            'has_secret' => $user->getGoogle2faSecret() !== null,
        ]);
    }
}
