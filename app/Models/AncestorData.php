<?php

namespace App\Models;

use App\Policies\PortsPolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class AncestorData extends Model
{
    use HasFactory;
    use HasRoles;

    protected $table = 'ancestor_data';
    protected $fillable = [
        'ancestor_surname',
        'given_name',
        'maiden_surname',
        'mode_of_travel_id',
        'date_of_death',
        'month_of_death',
        'place_of_birth',
        'place_of_death',
        'year_of_death',
        'first_date',
        'date_of_birth',
        'month_of_birth',
        'year_of_birth',
        'emigrant_no',
        'occupation',
        'source_of_arrival',
        'gender'
    ];

    public function mode_of_travel()
    {
        return $this->belongsTo(ModeOfArrivals::class, "mode_of_travel_id");
    }
    public function Gender()
    {
        return $this->belongsTo(Gender::class, "gender");
    }
    public function sourceOfArrival()
    {
        return $this->belongsTo(SourceOfArrival::class, "source_of_arrival");
    }

    public function county()
    {
        return $this->belongsTo(Counties::class, "from");
    }
    public function departureCountry()
    {
        return $this->belongsTo(Countries::class, "departure_country");
    }
    public function state()
    {
        return $this->belongsTo(States::class, 'arrival_state');
    }
    public function departureCity()
    {
        return $this->belongsTo(City::class, "departure_city");
    }
    public function arrivalCountry()
    {
        return $this->belongsTo(Countries::class, "arrival_country");
    }

    public function occupation_relation()
    {
        return $this->belongsTo(Occupation::class, "occupation");
    }
    public function Ships()
    {
        return $this->belongsTo(Ship::class, "source_of_arrival");
    }
    public function port()
    {
        return $this->belongsTo(Ports::class, "port");
    }

    public function localTravelDetails()
    {
        return $this->hasOne(AncestorLocalTravelDetail::class, 'ancestor_id');
    }
    public function spouse_details()
    {
        return $this->hasOne(AncestorSpouse::class, 'ancestor_id');
    }
    public function members()
    {
        return $this->belongsToMany(Member::class, 'member_ancestor', 'ancestor_id', 'member_id');
    }
    public function notes()
    {
        return $this->hasMany(AncestorNote::class, 'ancestor_id');
    }
}
