<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Console\Kernel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Pterodactyl\Exceptions\Model\DataValidationException;
use Pterodactyl\Exceptions\Repository\RecordNotFoundException;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Http\Requests\Admin\Settings\OAuthSettingsFormRequest;

class OAuthController extends Controller
{
    private AlertsMessageBag $alert;

    private Kernel $kernel;

    private SettingsRepositoryInterface $settings;

    /**
     * IndexController constructor.
     *
     * @param AlertsMessageBag $alert
     * @param Kernel $kernel
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(
        AlertsMessageBag $alert,
        Kernel $kernel,
        SettingsRepositoryInterface $settings)
    {
        $this->alert = $alert;
        $this->kernel = $kernel;
        $this->settings = $settings;
    }

    /**
     * Render the UI for basic Panel settings.
     *
     * @return View
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function index(): View
    {
        // Don't send the client_secret
        $drivers = json_decode(app('config')->get('pterodactyl.auth.oauth.drivers'), true);

        foreach ($drivers as $driver => $options) {
            unset($drivers[$driver]['client_secret']);
        }

        return view('admin.settings.oauth', [
            'drivers' => json_encode($drivers)
        ]);
    }

    /**
     * Handle settings update.
     *
     * @param OAuthSettingsFormRequest $request
     * @return RedirectResponse
     * @throws DataValidationException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws RecordNotFoundException
     */
    public function update(OAuthSettingsFormRequest $request): RedirectResponse
    {
        // Set current client_secret if empty
        $newDrivers = json_decode($request->normalize()['oauth:drivers'], true);
        $currentDrivers = json_decode(app('config')->get('pterodactyl.auth.oauth.drivers'), true);

        foreach ($newDrivers as $driver => $options) {
            if (!array_has($options, 'client_secret') || empty($options['client_secret'])) {
                $newDrivers[$driver]['client_secret'] = $currentDrivers[$driver]['client_secret'];
            }
        }

        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $key == 'oauth:drivers' ? json_encode($newDrivers) : $value);
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('OAuth settings have been updated successfully and the queue worker was restarted to apply these changes.')->flash();

        return redirect()->route('admin.settings.oauth');
    }
}
