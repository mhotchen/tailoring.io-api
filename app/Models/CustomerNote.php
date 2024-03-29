<?php
namespace App\Models;

use App\Models\Scopes\OrderByScope;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CustomerNote
 *
 * @property string                   $company_id
 * @property string                   $id
 * @property string                   $note
 * @property string                   $customer_id
 * @property string                   $created_by
 * @property string                   $updated_by
 * @property \Carbon\Carbon|null      $created_at
 * @property \Carbon\Carbon|null      $updated_at
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\User    $createdBy
 * @property-read \App\Models\User    $updatedBy
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CustomerNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CustomerNote whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CustomerNote whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CustomerNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CustomerNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CustomerNote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CustomerNote whereUpdatedBy($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CustomerNote whereCompanyId($value)
 */
final class CustomerNote extends Model
{
    use Compoships;

    /** @var array */
    protected $fillable = ['id', 'note'];

    /** @var array */
    protected $casts = ['id' => 'string'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new OrderByScope('created_at', 'ASC'));
    }

    /**
     * @param array        $request
     * @param User         $user
     * @param Company|null $company Only required when creating a new customer, otherwise it's ignored.
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \LogicException
     */
    public function hydrateFromRequest(array $request, User $user, Company $company = null): void
    {
        $this->fill($request['data']);

        if (!$this->exists) {
            $this->createdBy()->associate($user);

            if (!$company) {
                throw new \LogicException("'company' is required when creating a note");
            }

            $this->company()->associate($company);
        }

        if ($this->isDirty()) {
            $this->updatedBy()->associate($user);
        }
    }
}
