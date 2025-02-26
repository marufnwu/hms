<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Str;

/**
 * Class Bed
 *
 * @version February 17, 2020, 10:56 am UTC
 *
 * @property int $id
 * @property int $bed_type
 * @property int $bed_id
 * @property string|null $description
 * @property float $charge
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Bed newModelQuery()
 * @method static Builder|Bed newQuery()
 * @method static Builder|Bed query()
 * @method static Builder|Bed whereBedId($value)
 * @method static Builder|Bed whereBedType($value)
 * @method static Builder|Bed whereCharge($value)
 * @method static Builder|Bed whereCreatedAt($value)
 * @method static Builder|Bed whereDescription($value)
 * @method static Builder|Bed whereId($value)
 * @method static Builder|Bed whereUpdatedAt($value)
 *
 * @mixin Model
 *
 * @property-read BedType $bedType
 * @property int $name
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bed whereName($value)
 *
 * @property int $is_available
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Bed whereIsAvailable($value)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\BedAssign[] $bedAssigns
 * @property-read int|null $bed_assigns_count
 * @property int $is_default
 *
 * @method static Builder|Bed whereIsDefault($value)
 */
class Bed extends Model
{
    public $table = 'beds';

    public $fillable = [
        'bed_type',
        'bed_id',
        'description',
        'name',
        'charge',
        'is_available',
        'currency_symbol',
    ];

    protected $casts = [
        'id' => 'integer',
        'bed_type' => 'integer',
        'bed_id' => 'string',
        'description' => 'string',
        'name' => 'string',
        'charge' => 'double',
        'is_available' => 'integer',
        'currency_symbol' => 'string',
    ];

    const NOTAVAILABLE = 0;

    const AVAILABLE = 1;

    const AVAILABLE_ALL = 2;

    const STATUS_ARR = [
        self::AVAILABLE_ALL => 'All',
        self::AVAILABLE => 'Available',
        self::NOTAVAILABLE => 'Not Available',
    ];

    const FILTER_INCOME_HEAD = [
        0 => 'All',
        1 => 'Available',
        2 => 'Not Available',
    ];

    public static $rules = [
        'bed_type' => 'required',
        'name' => 'required|unique:beds,name',
        'charge' => 'required',
        'description' => 'string|nullable',
    ];

    public function patientNameRetrieved()
    {
        foreach ($this->bedAssigns as $bedAssign) {
            return $bedAssign->patient->patientUser->full_name;
        }
    }

    public function prepareData()
    {
        return [
            'bed_type' => $this->bed_type,
            'patient_name' => $this->patientNameRetrieved() ?? __('messages.common.n/a'),
        ];
    }

    public function prepareBedAssignData()
    {
        return [
            'bed_name' => $this->name,
            'patient' => $this->bedAssigns[0]->patient->patientUser->full_name ?? __('messages.common.n/a'),
            'phone' => $this->bedAssigns[0]->patient->patientUser->phone ?? __('messages.common.n/a'),
            'admission_date' => date('jS M, Y h:i A', strtotime($this->bedAssigns[0]->assign_date)) ?? __('messages.common.n/a'),
            'gender' => $this->bedAssigns[0]->patient->patientUser->gender ? 'Female' : 'Male' ?? __('messages.common.n/a'),
        ];
    }

    public function preparePatientAdmissionData()
    {
        return $this->patientAdmission[0]->prepareData();
    }

    public static function generateUniqueBedId()
    {
        $bedId = Str::random(8);
        while (true) {
            $isExist = self::whereBedId($bedId)->exists();
            if ($isExist) {
                self::generateUniqueBedId();
            }
            break;
        }

        return $bedId;
    }

    public function bedType(): BelongsTo
    {
        return $this->belongsTo(BedType::class, 'bed_type');
    }

    public function patientAdmission(): HasMany
    {
        return $this->hasMany(PatientAdmission::class, 'bed_id');
    }

    public function bedAssigns(): HasMany
    {
        return $this->hasMany(BedAssign::class, 'bed_id');
    }
}
