<?php
namespace App\Repositories\Utility;

use App\Models\Utility\IpFilter;
use Illuminate\Validation\ValidationException;

class IpFilterRepository
{
    protected $ip_filter;

    /**
     * Instantiate a new instance.
     *
     * @param IpFilter $ip_filter
     */
    public function __construct(
        IpFilter $ip_filter
    ) {
        $this->ip_filter = $ip_filter;
    }

    /**
     * Get all ip filter
     *
     * @return IpFilter[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return $this->ip_filter->all();
    }

    /**
     * Count ip filter
     *
     * @return User
     */
    public function count()
    {
        return $this->ip_filter->count();
    }

    /**
     * Find ip filter with given id or throw an error.
     *
     * @param integer $id
     * @return IpFilter
     * @throws ValidationException
     */
    public function findOrFail($id)
    {
        $ip_filter = $this->ip_filter->findOrFail($id);

        if (! $ip_filter) {
            throw ValidationException::withMessages(['message' => trans('utility.could_not_find_ip_filter')]);
        }

        return $ip_filter;
    }

    /**
     * Find ip filter by Id
     *
     * @param integer $id
     * @return IpFilter
     */
    public function invertFind($id)
    {
        return $this->ip_filter->where('id', '!=', $id)->get();
    }

    /**
     * Paginate all ip filters using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = gv($params, 'sort_by', 'created_at');
        $order       = gv($params, 'order', 'desc');
        $page_length = gv($params, 'page_length', config('config.page_length'));

        return $this->ip_filter->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new ip filter.
     *
     * @param array $params
     * @return IpFilter
     * @throws ValidationException
     */
    public function create($params)
    {
        return $this->ip_filter->forceCreate($this->formatParams($params));
    }

    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     * @throws ValidationException
     */
    private function formatParams($params, $action = 'create', $id = null)
    {
        $start_ip    = gv($params, 'start_ip');
        $end_ip      = gv($params, 'end_ip');
        $description = gv($params, 'description');

        $validate = $this->validateIp(ip2long($start_ip), $end_ip ? ip2long($end_ip) : null, ($action === 'update' ? $id : null));

        if ($validate['status'] === 'error') {
            throw ValidationException::withMessages($validate['message']);
        }

        $formatted = [
            'start_ip'    => $start_ip,
            'end_ip'      => $end_ip,
            'description' => $description
        ];

        return $formatted;
    }

    /**
     * Update given ip filter.
     *
     * @param IpFilter $ip_filter
     * @param array $params
     *
     * @return IpFilter
     * @throws ValidationException
     */
    public function update(IpFilter $ip_filter, $params)
    {
        $ip_filter->forceFill($this->formatParams($params, 'update', $ip_filter->id))->save();

        return $ip_filter;
    }

    /**
     * Delete ip filter.
     *
     * @param integer $id
     * @return bool|null
     * @throws \Exception
     */
    public function delete(IpFilter $ip_filter)
    {
        return $ip_filter->delete();
    }

    /**
     * Delete multiple ip filters.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->ip_filter->whereIn('id', $ids)->delete();
    }

    /**
     * Validate given Ip range.
     *
     * @param ip $start_ip
     * @param ip $end_ip
     * @param null $id
     * @return array
     */
    public function validateIp($start_ip, $end_ip, $id = null)
    {
        if ($end_ip && $start_ip > $end_ip) {
            $response = ['status' => 'error', 'message' => ['start_ip' => [trans('utility.invalid_ip_range')]]];
            return $response;
        }

        $start_ip_same = 0;
        $start_ip_in_range = 0;
        $end_ip_in_range = 0;
        $other_ip_in_range = 0;

        if (!$id) {
            $ips = $this->getAll();
        } else {
            $ips = $this->invertFind($id);
        }

        foreach ($ips as $ip) {
            $all_start_ip = ip2long($ip->start_ip);
            $all_end_ip = ($ip->end_ip) ? ip2long($ip->end_ip) : null;

            if ($all_start_ip === $start_ip) {
                $start_ip_same++;
            } elseif ($end_ip && !$all_end_ip && $start_ip <= $all_start_ip && $end_ip >= $all_start_ip) {
                $other_ip_in_range++;
            } elseif ($all_end_ip && $start_ip >= $all_start_ip && $start_ip <= $all_end_ip) {
                $start_ip_in_range++;
            } elseif ($end_ip && $end_ip >= $all_start_ip && $end_ip <= $all_end_ip) {
                $end_ip_in_range++;
            } elseif ($end_ip && $start_ip < $all_start_ip && $end_ip > $all_end_ip) {
                $other_ip_in_range++;
            }
        }

        if ($start_ip_same) {
            $response = ['status' => 'error', 'message' => ['start_ip' => [trans('utility.start_ip_same')]]];
        } elseif ($start_ip_in_range) {
            $response = ['status' => 'error', 'message' => ['start_ip' => [trans('utility.start_ip_in_range')]]];
        } elseif ($end_ip_in_range) {
            $response = ['status' => 'error', 'message' => ['end_ip' => [trans('utility.end_ip_in_range')]]];
        } elseif ($other_ip_in_range) {
            $response = ['status' => 'error', 'message' => ['end_ip' => [trans('utility.other_ip_in_range')]]];
        } else {
            $response = ['status' => 'success'];
        }

        return $response;
    }
}
