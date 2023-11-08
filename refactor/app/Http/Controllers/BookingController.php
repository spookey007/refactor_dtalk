<?php

namespace DTApi\Http\Controllers;

use App\Http\Requests\{BookingCreate, DistanceFeedRequest, UpdateJob};
use DTApi\Models\Job;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected $repository,$distanceRepository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository,DistanceRepository $distanceRepository)
    {
        $this->distanceRepository = $distanceRepository;
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $user_id = $request->get('user_id');
        $user_type = $request->__authenticatedUser->user_type;
        $response = null;
        
        if ($user_id) {
            $response = $this->repository->getUsersJobs($user_id);
        } 
        elseif ($user_type == config('app.admin_role_id') || $user_type ==  config('app.superadmin_role_id')) {
            $response = $this->repository->getAll($request);
        }
        
        return response($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);
        return response($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(BookingCreate $request)
    {
        $data = $request->validated();

        $response = $this->repository->store($request->__authenticatedUser, $data);

        return response($response);

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, UpdateJob $request)
    {
        $data =  $request->validated();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();

        $response = $this->repository->storeJobEmail($data);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(JobsHistoryRequest $request)
    {
        $user_id = $request->input('user_id');
        $response = $this->repository->getUsersJobsHistory($user_id, $request);
        return response($response)
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->repository->acceptJobWithId($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->endJob($data);

        return response($response);

    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->repository->customerNotCall($data);

        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(DistanceFeedRequest $request)
    {
        $validatedData = $request->validated();
        $jobId = $validatedData['jobid'];

        $distance = $validatedData['distance'] ?? '';
        $time = $validatedData['time'] ?? '';

        $session = $validatedData['session_time'] ?? '';

        $flagged = $validatedData['flagged'] ? 'yes' : 'no';
        $manuallyHandled = $validatedData['manually_handled'] ? 'yes' : 'no';
        $byAdmin = $validatedData['by_admin'] ? 'yes' : 'no';

        $adminComment = $validatedData['admincomment'] ?? '';

        if (!empty($time) || !empty($distance)) {
            $this->distanceRepository->updateDistance($jobId, $distance, $time);
        }

        if (!empty($adminComment) || !empty($session) || !empty($flagged) || !empty($manuallyHandled) || !empty($byAdmin)) {
            $this->repository->updateJobComment($jobId, $adminComment, $flagged, $session, $manuallyHandled, $byAdmin);
        }

        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
