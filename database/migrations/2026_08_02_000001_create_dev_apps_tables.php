<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Developer portal tables.
 *
 * A "dev app" is an externally-built application (its own Git repo, its own
 * container on Dokploy) that datakita mounts under a path on its own domain
 * and gates with its own login. datakita never runs the code — it only owns
 * the routing decision and the "who may see this" decision.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dev_apps', function (Blueprint $table) {
            // UUID keys throughout, matching users and the survey tables.
            $table->uuid('id')->primary();

            // ── Identity ────────────────────────────────────────────────
            // slug doubles as the URL path segment: https://<host>/<slug>
            $table->string('slug', 60)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->foreignUuid('owner_user_id')->constrained('users')->cascadeOnDelete();

            // ── Source ──────────────────────────────────────────────────
            $table->string('git_repo', 500);
            $table->string('git_branch', 100)->default('main');
            $table->string('git_build_path', 255)->default('/');
            // dockerfile | nixpacks | heroku_buildpacks | paketo
            $table->string('build_type', 30)->default('nixpacks');
            $table->string('dockerfile_path', 255)->nullable();
            // Dokploy SSH key id, required only for private repos.
            $table->string('ssh_key_id', 100)->nullable();

            // ── Runtime ─────────────────────────────────────────────────
            $table->unsignedSmallInteger('container_port')->default(3000);
            // Strip "/<slug>" before forwarding, so an app written to run at
            // the domain root keeps working unmodified. Off for apps that
            // already know their own base path.
            $table->boolean('strip_prefix')->default(true);

            // ── Access control (the switch the portal exposes) ───────────
            // public | login_required | role | allowlist | owner_only
            $table->string('auth_mode', 30)->default('login_required');
            $table->json('allowed_roles')->nullable();

            // ── Dokploy linkage ─────────────────────────────────────────
            $table->string('dokploy_project_id', 100)->nullable();
            $table->string('dokploy_application_id', 100)->nullable();
            $table->string('dokploy_app_name', 150)->nullable();
            $table->string('dokploy_domain_id', 100)->nullable();

            // ── State ───────────────────────────────────────────────────
            // draft | provisioning | deploying | running | failed | stopped
            $table->string('status', 20)->default('draft');
            $table->boolean('enabled')->default(true);
            $table->timestamp('last_deployed_at')->nullable();
            $table->text('last_error')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('owner_user_id');
        });

        // Explicit per-user grants, used when auth_mode = allowlist.
        // No surrogate key: the pair IS the identity, and a composite primary
        // key makes a duplicate grant impossible at the storage layer.
        Schema::create('dev_app_allowed_users', function (Blueprint $table) {
            $table->foreignUuid('dev_app_id')->constrained('dev_apps')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['dev_app_id', 'user_id']);
        });

        // One row per deploy attempt, so the portal can show history + logs.
        Schema::create('dev_app_deployments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('dev_app_id')->constrained('dev_apps')->cascadeOnDelete();
            $table->foreignUuid('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dokploy_deployment_id', 100)->nullable();
            // queued | running | success | failed
            $table->string('status', 20)->default('queued');
            $table->longText('log')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['dev_app_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dev_app_deployments');
        Schema::dropIfExists('dev_app_allowed_users');
        Schema::dropIfExists('dev_apps');
    }
};
