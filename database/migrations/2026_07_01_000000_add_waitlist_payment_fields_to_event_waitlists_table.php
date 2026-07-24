<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('event_waitlists', function (Blueprint $table) {
            if (!Schema::hasColumn('event_waitlists', 'role_name')) {
                $table->string('role_name')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('event_waitlists', 'payment_amount')) {
                $table->decimal('payment_amount', 12, 2)->default(0)->after('role_name');
            }
        });
    }

    public function down()
    {
        Schema::table('event_waitlists', function (Blueprint $table) {
            if (Schema::hasColumn('event_waitlists', 'payment_amount')) {
                $table->dropColumn('payment_amount');
            }
            if (Schema::hasColumn('event_waitlists', 'role_name')) {
                $table->dropColumn('role_name');
            }
        });
    }
};
