<?php

namespace App\Ai\Agents;

use Illuminate\Support\Facades\Schema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class UniversalDbAgent implements Agent
{
    use Promptable;

    public function __construct(public string $tableName) {}

    public function instructions(): string
    {
        // ডায়নামিকভাবে কলামগুলো বের করা
        $columns = Schema::getColumnListing($this->tableName);
        $fillable = array_diff($columns, ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token']);

        return "You are a database entry assistant for the table: '{$this->tableName}'.
                Strictly use these columns: [".implode(', ', $fillable).'].
                Task: Extract data from user input and map accurately to the columns.
                Constraints: 
                1. If a value is missing, return null.
                2. If a value is a date, use YYYY-MM-DD format.
                3. ONLY return a valid JSON object. No prose or markdown code blocks.
                JSON Structure: {"data": {"column_name": "value"}}';
    }

    public function provider(): string
    {
        return 'gemini'; // config/ai.php তে জেমিনি কনফিগার করা থাকতে হবে
    }
}
