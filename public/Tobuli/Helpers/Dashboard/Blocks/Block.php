<?php namespace Tobuli\Helpers\Dashboard\Blocks;

use Illuminate\Support\Arr;

abstract class Block implements BlockInterface
{
    protected $user;

    protected $settings;

    abstract protected function getContent();
    abstract protected function getName();

    
    public function __construct()
    {
        $this->user = auth()->user();
        $this->settings = $this->user->getSettings("dashboard.blocks." . $this->getName());
        $this->settings['enabled'] = $this->settings['enabled'] && $this->isEnabled();
    }

    public function buildFrame()
    {
        $name = $this->getName();

        return view("front::Dashboard.Blocks.$name.block")->with([
            'name'    => $name,
            'config' => $this->getConfig(),
        ])->render();
    }

    public function buildContent()
    {
        if (is_null($content = $this->getContent()))
            return null;

        return view('front::Dashboard.Blocks.' . $this->getName() . '.content' )
            ->with($content)
            ->render();
    }

    public function getConfig($key = null)
    {
        if (is_null($key))
            return $this->settings;

        return Arr::get($this->settings, $key);
    }


    protected function isEnabled(): bool
    {
        return true;
    }
    
    /**
     * Normalize date options to use today's date if not set
     * 
     * @param array $options
     * @return array
     */
    protected function normalizeDateOptions($options)
    {
        if (!isset($options['from_date']) || empty($options['from_date'])) {
            $options['from_date'] = \Carbon\Carbon::today()->format('Y-m-d');
        }
        if (!isset($options['to_date']) || empty($options['to_date'])) {
            $options['to_date'] = \Carbon\Carbon::today()->format('Y-m-d');
        }
        return $options;
    }
}