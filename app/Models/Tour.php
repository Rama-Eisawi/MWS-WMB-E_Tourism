<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;
    protected $fillable = ['program_id', 'guide_id', 'driver_id', 'price', 'number', 'start_date', 'end_date'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
    public function guide()
    {
        return $this->belongsTo(Guide::class);
    }
    public function program()
    {
        return $this->belongsTo(Program::class);
    }
    public function tourists()
    {
        return $this->hasMany(Tourist::class);
    }
    /**
     * Scope a query to only include available tours.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query, $startDate = null, $endDate = null)
    {
        // Filter tours with fewer than 50 participants and a future start date
        $query->where('number', '<', 50)
            ->where('start_date', '>', Carbon::now());

        // Apply date range filter if provided
        if ($startDate && $endDate) {
            $this->filterByDateRange($query, $startDate, $endDate);
        }

        return $query;
    }
    private function filterByDateRange($query, $startDate, $endDate)
    {
        $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                    $subQuery->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });
    }
}
