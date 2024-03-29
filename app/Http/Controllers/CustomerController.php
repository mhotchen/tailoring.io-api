<?php
namespace App\Http\Controllers;

use App\Http\Requests\CustomerStoreRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Company;
use App\Models\Customer;
use App\Models\MeasurementProfile;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class CustomerController extends Controller
{
    /**
     * @param Company $company
     * @param Request $request
     * @return ResourceCollection
     * @throws \InvalidArgumentException
     */
    public function index(Company $company, Request $request): ResourceCollection
    {
        return CustomerResource::collection($company->findCustomers($request->query('q')))
            ->additional([
                'meta' => ['total_customers' => $company->customers()->count()]
            ]);
    }

    /**
     * @param Company $company
     * @param string  $id
     * @return CustomerResource
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get(Company $company, string $id): CustomerResource
    {
        $customer = $company
            ->customers()
            ->with([
                'notes',
                'measurementProfiles.commits.measurements.setting',
                'measurementProfiles.commits.sampleGarment',
            ])
            ->findOrFail($id);

        return new CustomerResource($customer);
    }

    /**
     * @param CustomerStoreRequest $customerStoreRequest
     * @param Company              $company
     * @return CustomerResource
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Throwable
     */
    public function post(CustomerStoreRequest $customerStoreRequest, Company $company): CustomerResource
    {
        $request = $customerStoreRequest->validated();
        $customer = new Customer;
        $customer->hydrateFromRequest($request, Auth::user(), $company);
        $notesData = collect($request['data']['notes'])->filter(function(array $data) {
            return isset($data['data']['note']) && $data['data']['note'] !== '';
        });
        $bodyMeasurementProfile = new MeasurementProfile;
        $bodyMeasurementProfile->fillForBodyProfile($company, Auth::user());

        DB::transaction(function () use ($customer, $notesData, $bodyMeasurementProfile, $company) {
            $customer->save();
            $customer->createNewNotes($notesData, Auth::user(), $company);
            $customer->measurementProfiles()->save($bodyMeasurementProfile);
            $customer->load('notes');
        });

        return new CustomerResource($customer);
    }

    /**
     * @param CustomerStoreRequest $customerStoreRequest
     * @param Company              $company
     * @param string               $id
     * @return CustomerResource
     * @throws \Throwable
     */
    public function put(CustomerStoreRequest $customerStoreRequest, Company $company, string $id): CustomerResource
    {
        $request = $customerStoreRequest->validated();
        /** @var Customer $customer */
        $customer = $company->customers()->findOrFail($id);

        // Ignore the ID field in the request, use the one from the loaded model instead.
        unset($request['data']['id']);

        $customer->hydrateFromRequest($request, Auth::user());
        $notesData = collect($request['data']['notes'])->filter(function(array $data) {
            return isset($data['data']['note']) && $data['data']['note'] !== '';
        });

        DB::transaction(function () use ($customer, $notesData, $company) {
            $customer->save();
            $customer->loadMissing('notes');
            $customer
                ->deleteClearedNotes($notesData)
                ->updateExistingNotes($notesData, Auth::user())
                ->createNewNotes($notesData, Auth::user(), $company);
            $customer->load('notes');
        });

        return new CustomerResource($customer);
    }
}
