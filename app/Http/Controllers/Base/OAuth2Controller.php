<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\Support\Str;
use Illuminate\Auth\AuthManager;
use Illuminate\Support\MessageBag;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Hashing\Hasher;
use Laravel\Socialite\Facades\Socialite;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Traits\Helpers\OAuth2Providers;
use Pterodactyl\Services\Users\UserUpdateService;
use Pterodactyl\Services\Users\UserCreationService;
use Pterodactyl\Contracts\Repository\UserRepositoryInterface;

class OAuth2Controller extends Controller
{
    use OAuth2Providers;

    /**
     * @var \Pterodactyl\Services\Users\UserCreationService
     */
    protected $creationService;

    /**
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    private $hasher;

    /**
     * @var \Illuminate\Auth\AuthManager
     */
    private $auth;

    /**
     * @var \Pterodactyl\Contracts\Repository\UserRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Pterodactyl\Services\Users\UserUpdateService
     */
    protected $updateService;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * OAuth2Controller constructor.
     *
     * @param \Pterodactyl\Services\Users\UserCreationService $creationService
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param \Illuminate\Auth\AuthManager $auth
     * @param \Pterodactyl\Contracts\Repository\UserRepositoryInterface $repository
     * @param \Pterodactyl\Services\Users\UserUpdateService $updateService
     * @param AlertsMessageBag $alert
     */
    public function __construct(UserCreationService $creationService, Hasher $hasher, AuthManager $auth, UserRepositoryInterface $repository, UserUpdateService $updateService, AlertsMessageBag $alert)
    {
        $this->creationService = $creationService;
        $this->hasher = $hasher;
        $this->auth = $auth;
        $this->repository = $repository;
        $this->updateService = $updateService;
        $this->alert = $alert;
    }

    /**
     * Obtain the user information and login
     * or redirect the user to the authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function login()
    {
        // If this feature is not enabled or the user is already signed in and isn't linking a new account redirect or abort
        if ($this->auth->guard()->check() and ! session()->has('link_oauth2_driver')) {
            return redirect()->route('index');
        }
        if (! config('oauth2.enabled')) {
            abort(404);
        }

        // If linking a new provider form the account page
        if (session()->has('link_oauth2_driver')) {
            $session_driver = session()->get('link_oauth2_driver');
            session()->forget('link_oauth2_driver');

            // Check if authenticated
            if (! $this->auth->guard()->check()) {
                $errors = new MessageBag(['user' => [__('auth.failed')]]);

                return redirect()->route('auth.login')
                    ->withErrors($errors);
            }

            $user = $this->auth->guard()->user();

            // Get the user info from OAuth2
            try {
                $socialite_user = Socialite::driver($session_driver)->user();
            } catch (\Exception $exception) {
                $this->alert->danger(trans('base.account.oauth2_link_failed'))->flash();

                return redirect()->route('account');
            }

            $oauth2_socialite_id = $socialite_user->getId();

            $new_ids = [];
            $done = false;
            // Replace if already exists
            foreach (preg_split('~,~', $user->getAttributes('oauth2_id')) as $id) {
                if (Str::startsWith($id, $session_driver)) {
                    $id = $session_driver . ':' . $oauth2_socialite_id;
                    $done = true;
                }
                $new_ids = array_merge($new_ids, [$id]);
            }

            // Add if doesnt exist
            if (! $done) {
                $new_ids = array_merge($new_ids, [$session_driver . ':' . $oauth2_socialite_id]);
            }

            $oauth2_id = implode(',', $new_ids);

            try {
                $this->updateService->handle($user, compact('oauth2_id'));
            } catch (\Exception $e) {
                $this->alert->danger(trans('base.account.oauth2_link_failed'))->flash();

                return redirect()->route('account');
            }

            $this->alert->success(trans('base.account.details_updated'))->flash();

            return redirect()->route('account');
        }

        // Get the current OAuth2 user else redirect to auth page
        if (! session()->has('oauth2_driver')) {
            return redirect()->route('auth.login');
        }

        $session_driver = session()->get('oauth2_driver');
        session()->forget('oauth2_driver');

        try {
            $socialite_user = Socialite::driver($session_driver)->user();
        } catch (\Exception $exception) {
            return redirect()->route('auth.login');
        }

        // The user's Id.
        $oauth2_id = $socialite_user->getId();

        try {
            // Try to get the user
            $user = $this->repository->findFirstWhere([['oauth2_id', 'LIKE', '%' . $session_driver . ':' . $oauth2_id . '%']]);

            // Login
            $this->auth->guard()->login($user);
            if ($this->auth->guard()->check()) {
                return redirect()->route('index');
            }
        } catch (\Exception $e) {
        }

        // Invalid Login
        $errors = new MessageBag(['user' => [__('auth.failed')]]);

        return redirect()->route('auth.login')
            ->withErrors($errors);
    }
}
