<?php

namespace Pterodactyl\Tests\Integration\Api\Client\Server;

use Mockery;
use Illuminate\Http\Response;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Permission;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;
use Pterodactyl\Tests\Integration\Api\Client\ClientApiIntegrationTestCase;

class SettingsControllerTest extends ClientApiIntegrationTestCase
{
    /**
     * Test that the server's name can be changed.
     *
     * @param array $permissions
     * @dataProvider renamePermissionsDataProvider
     */
    public function testServerNameCanBeChanged($permissions)
    {
        /** @var \Pterodactyl\Models\Server $server */
        [$user, $server] = $this->generateTestAccount($permissions);
        $originalName = $server->name;

        $response = $this->actingAs($user)->postJson("/api/client/servers/{$server->uuid}/settings/rename", [
            'name' => '',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonPath('errors.0.meta.rule', 'required');

        $server = $server->refresh();
        $this->assertSame($originalName, $server->name);

        $this->actingAs($user)
            ->postJson("/api/client/servers/{$server->uuid}/settings/rename", [
                'name' => 'Test Server Name',
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $server = $server->refresh();
        $this->assertSame('Test Server Name', $server->name);
    }

    /**
     * Test that the server's description can be changed.
     *
     * @param array $permissions
     * @dataProvider changeDescriptionPermissionsDataProvider
     */
    public function testServerDescriptionCanBeChanged($permissions)
    {
        /** @var \Pterodactyl\Models\Server $server */
        [$user, $server] = $this->generateTestAccount($permissions);
        $originalDescription = $server->description;

        $response = $this->actingAs($user)->postJson("/api/client/servers/{$server->uuid}/settings/change-description", [
            'description' => '',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonPath('errors.0.meta.rule', 'required');

        $server = $server->refresh();
        $this->assertSame($originalDescription, $server->description);

        $this->actingAs($user)
            ->postJson("/api/client/servers/{$server->uuid}/settings/change-description", [
                'description' => 'Test Server Description',
            ])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $server = $server->refresh();
        $this->assertSame('Test Server Description', $server->description);
    }

    /**
     * Test that a subuser receives a permissions error if they do not have the required permission
     * and attempt to change the name.
     */
    public function testSubuserCannotChangeServerNameWithoutPermission()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_WEBSOCKET_CONNECT]);
        $originalName = $server->name;

        $this->actingAs($user)
            ->postJson("/api/client/servers/{$server->uuid}/settings/rename", [
                'name' => 'Test Server Name',
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $server = $server->refresh();
        $this->assertSame($originalName, $server->name);
    }

    /**
     * Test that a server can be reinstalled. Honestly this test doesn't do much of anything other
     * than make sure the endpoint works since.
     *
     * @param array $permissions
     * @dataProvider reinstallPermissionsDataProvider
     */
    public function testServerCanBeReinstalled($permissions)
    {
        /** @var \Pterodactyl\Models\Server $server */
        [$user, $server] = $this->generateTestAccount($permissions);
        $this->assertTrue($server->isInstalled());

        $service = Mockery::mock(DaemonServerRepository::class);
        $this->app->instance(DaemonServerRepository::class, $service);

        $service->expects('setServer')
            ->with(Mockery::on(function ($value) use ($server) {
                return $value->uuid === $server->uuid;
            }))
            ->andReturnSelf()
            ->getMock()
            ->expects('reinstall')
            ->andReturnUndefined();

        $this->actingAs($user)->postJson("/api/client/servers/{$server->uuid}/settings/reinstall")
            ->assertStatus(Response::HTTP_ACCEPTED);

        $server = $server->refresh();
        $this->assertSame(Server::STATUS_INSTALLING, $server->status);
    }

    /**
     * Test that a subuser receives a permissions error if they do not have the required permission
     * and attempt to reinstall a server.
     */
    public function testSubuserCannotReinstallServerWithoutPermission()
    {
        [$user, $server] = $this->generateTestAccount([Permission::ACTION_WEBSOCKET_CONNECT]);

        $this->actingAs($user)
            ->postJson("/api/client/servers/{$server->uuid}/settings/reinstall")
            ->assertStatus(Response::HTTP_FORBIDDEN);

        $server = $server->refresh();
        $this->assertTrue($server->isInstalled());
    }

    public function renamePermissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_SETTINGS_RENAME]]];
    }

    public function changeDescriptionPermissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_SETTINGS_CHANGE_DESCRIPTION]]];
    }

    public function reinstallPermissionsDataProvider(): array
    {
        return [[[]], [[Permission::ACTION_SETTINGS_REINSTALL]]];
    }
}
