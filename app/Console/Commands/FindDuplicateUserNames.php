<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FindDuplicateUserNames extends Command
{
    protected $signature = 'simulate:find-duplicate-names {--fix : Attempt to auto-rename duplicates by appending numeric suffix}';

    protected $description = 'Find duplicate user.name values and optionally suggest or fix them.';

    public function handle(): int
    {
        $this->info('Searching for duplicate user names...');

        $duplicates = DB::table('users')
            ->select('name', DB::raw('COUNT(*) as total'), DB::raw('GROUP_CONCAT(id) as ids'))
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate names found.');
            return 0;
        }

        foreach ($duplicates as $row) {
            $this->line("Name: {$row->name} — Count: {$row->total} — IDs: {$row->ids}");
            if ($this->option('fix')) {
                $ids = explode(',', $row->ids);
                // Keep the first ID as-is, rename others
                array_shift($ids);
                $suffix = 1;
                foreach ($ids as $id) {
                    $newName = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '', $row->name)) . '-' . $suffix;
                    while (User::where('name', $newName)->exists()) {
                        $suffix++;
                        $newName = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '', $row->name)) . '-' . $suffix;
                    }
                    User::where('id', $id)->update(['name' => $newName]);
                    $this->line("  Renamed id {$id} -> {$newName}");
                    $suffix++;
                }
            }
        }

        $this->info('Done.');

        return 0;
    }
}
