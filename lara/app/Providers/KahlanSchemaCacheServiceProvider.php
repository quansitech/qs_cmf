<?php
/**
 * KahlanSchemaCacheServiceProvider —— migrate 后自动刷新 Kahlan schema 缓存。
 *
 * 设计动机：见 App\Support\SchemaCacheBuilder 的类注释。本 provider 把「跑完正向迁移
 * 后重新生成 schema 缓存」挂到 Laravel 迁移事件流上，让开发者在 dev/testing 环境只需
 * 正常 `php artisan migrate`，缓存自动保持最新——无需记着跑额外命令。
 *
 * 触发点：MigrationsEnded 事件（CmmMigrator 在每批正向迁移完成后 dispatch，
 * method='up'；rollback 时 method='down'）。只在 method='up' 时刷新——回滚本身不应
 * 刷新缓存（表可能已被 drop），但之后再次 migrate 会触发 up、刷新缓存，符合预期。
 *
 * production 守卫：boot() 首行检查环境，production 直接 return，listener 根本不注册，
 * 零开销。.env 的 APP_ENV=production 即生效（lara/config/app.php 'env' 读此值）。
 *
 * 缓存缺失兜底：若从未跑过 migrate（新 clone 必跑 migrate，理论上不会发生），
 * lara/specs/bootstrap.php 的 loadCachedSchema() 检测文件缺失会静默跳过，不影响现有 spec。
 */

namespace App\Providers;

use App\Support\SchemaCacheBuilder;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class KahlanSchemaCacheServiceProvider extends ServiceProvider
{
    /**
     * 注册迁移事件监听器。
     *
     * 用 boot() 而非 register()：事件监听需在框架事件系统就绪后注册。
     */
    public function boot(): void
    {
        // production 直接跳过：不注册 listener，artisan migrate 在生产环境无任何额外开销。
        if ($this->app->environment('production')) {
            return;
        }

        // 正向迁移完成后刷新缓存。
        // - method='up'：migrate / migrate:fresh / migrate:refresh 的正向阶段触发；
        // - method='down'：rollback 触发，跳过（回滚不应刷新缓存）。
        Event::listen(MigrationsEnded::class, function (MigrationsEnded $event): void {
            if ($event->method !== 'up') {
                return;
            }

            try {
                SchemaCacheBuilder::build();
            } catch (\Throwable $e) {
                // 缓存生成失败不应中断 migrate 流程。写错误到 stderr 供排查，
                // 开发者可手动跑 build() 修复。
                fwrite(STDERR, "[KahlanSchemaCache] 生成缓存失败: " . $e->getMessage() . "\n");
            }
        });
    }

    /**
     * 无容器绑定（缓存生成为一次性副作用，不需要注册服务）。
     */
    public function register(): void
    {
    }
}
