<?php
namespace App\Repositories\Utility;

use App\Models\Utility\EmailTemplate;
use App\Repositories\Configuration\ConfigurationRepository;

class EmailTemplateRepository
{
    protected $email_template;
    protected $config;

    /**
     * Instantiate a new instance.
     *
     * @return void
     */
    public function __construct(
        EmailTemplate $email_template,
        ConfigurationRepository $config
    ) {
        $this->email_template = $email_template;
        $this->config        = $config;
    }

    /**
     * Find email template with given id or throw an error.
     *
     * @param integer $id
     * @return EmailTemplate
     */
    public function findOrFail($id)
    {
        $email_template = $this->email_template->find($id);

        if (! $email_template) {
            throw ValidationException::withMessages(['message' => trans('utility.could_not_find_email_template')]);
        }

        return $email_template;
    }

    /**
     * Find by slug.
     *
     * @return EmailTemplate
     */
    public function findBySlug($slug)
    {
        return $this->email_template->filterBySlug($slug)->first();
    }

    /**
     * List email template by category.
     *
     * @param string $category
     * @return array
     */
    public function listByCategory($category = null)
    {
        return $this->email_template->whereCategory($category ? : 'user')->get(['name','id']);
    }

    /**
     * Paginate all email templates using given params.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($params)
    {
        $sort_by     = gv($params, 'sort_by', 'created_at');
        $order       = gv($params, 'order', 'desc');
        $page_length = gv($params, 'page_length', config('config.page_length'));

        return $this->email_template->orderBy($sort_by, $order)->paginate($page_length);
    }

    /**
     * Create a new email template.
     *
     * @param array $params
     * @return EmailTemplate
     */
    public function create($params)
    {
        return $this->email_template->forceCreate($this->formatParams($params));
    }

    /**
     * Prepare given params for inserting into database.
     *
     * @param array $params
     * @param string $type
     * @return array
     */
    private function formatParams($params, $action = 'create')
    {
        if ($action === 'create') {
            $formatted = [
                'name'     => gv($params, 'name'),
                'category' => gv($params, 'category'),
                'slug'     => createSlug(gv($params, 'name'))
            ];
        } elseif ($action === 'update') {
            $formatted = [
                'subject' => gv($params, 'subject'),
                'body'    => stripInlineStyle(gv($params, 'body'))
            ];
        }

        return $formatted;
    }

    /**
     * Update given email template.
     *
     * @param EmailTemplate $email_template
     * @param array $params
     *
     * @return EmailTemplate
     */
    public function update(EmailTemplate $email_template, $params)
    {
        $email_template->forceFill($this->formatParams($params, 'update'))->save();

        return $email_template;
    }

    /**
     * Find email template with given id which is deletable or throw an error.
     *
     * @param integer $id
     * @return EmailTemplate
     */
    public function deletable($id)
    {
        return $this->email_template->whereId($id)->whereIsDefault(0)->firstOrFail();
    }

    /**
     * Delete email template.
     *
     * @param integer $id
     * @return bool|null
     */
    public function delete(EmailTemplate $email_template)
    {
        return $email_template->delete();
    }

    /**
     * Delete multiple email template.
     *
     * @param array $ids
     * @return bool|null
     */
    public function deleteMultiple($ids)
    {
        return $this->email_template->whereIn('id', $ids)->delete();
    }

    /**
     * Get content for Email.
     *
     * @param array $params
     * @return array
     */
    public function getContent($params = array())
    {
        $template = gv($params, 'template');
        $user     = gv($params, 'user');
        $password = gv($params, 'password');

        $body    = $template->body;
        $subject = $template->subject;

        $company_logo    = $this->config->getCompanyLogo();
        $company_address = $this->config->getCompanyAddress();

        $body = str_replace('[COMPANY_LOGO]', $company_logo, $body);
        
        $body    = str_replace('[COMPANY_NAME]', config('config.company_name'), $body);
        $subject = str_replace('[COMPANY_NAME]', config('config.company_name'), $subject);
        
        $body    = str_replace('[COMPANY_EMAIL]', config('config.email'), $body);
        $subject = str_replace('[COMPANY_EMAIL]', config('config.email'), $subject);
        
        $body    = str_replace('[COMPANY_PHONE]', config('config.phone'), $body);
        $subject = str_replace('[COMPANY_PHONE]', config('config.phone'), $subject);
        
        $body    = str_replace('[COMPANY_WEBSITE]', config('config.website'), $body);
        $subject = str_replace('[COMPANY_WEBSITE]', config('config.website'), $subject);
        
        $body    = str_replace('[COMPANY_ADDRESS]', $company_address, $body);
        $subject = str_replace('[COMPANY_ADDRESS]', $company_address, $subject);
        
        $body    = str_replace('[CURRENT_DATE]', showDate(date('Y-m-d')), $body);
        $subject = str_replace('[CURRENT_DATE]', showDate(date('Y-m-d')), $subject);
        
        $body    = str_replace('[CURRENT_DATE_TIME]', showDateTime(date('Y-m-d H:i:s')), $body);
        $subject = str_replace('[CURRENT_DATE_TIME]', showDateTime(date('Y-m-d H:i:s')), $subject);

        if ($template->category === 'user' && $user) {
            $body    = str_replace('[NAME]', ($user->name) ? : '-', $body);
            $subject = str_replace('[NAME]', ($user->name) ? : '-', $subject);

            $body    = str_replace('[PASSWORD]', $password, $body);
            $subject = str_replace('[PASSWORD]', $password, $subject);

            $body    = str_replace('[EMAIL]', $user->email, $body);
            $subject = str_replace('[EMAIL]', $user->email, $subject);
        }

        $mail_data['body']    = $body;
        $mail_data['subject'] = $subject;

        return $mail_data;
    }
}
