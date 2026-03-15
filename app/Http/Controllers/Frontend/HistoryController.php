<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use CustomFacades\ModalHelpers\HistoryModalHelper;
use Formatter;

class HistoryController extends Controller
{
    public function index()
    {
        Formatter::byUser($this->user);
        if ($this->api)
            return HistoryModalHelper::getApi();

        $data = HistoryModalHelper::get();

        return view('front::History.index')->with($data);
    }

    public function positionsPaginated()
    {
        Formatter::byUser($this->user);
        $data = HistoryModalHelper::getMessages();

        return !$this->api ? view('front::History.partials.bottom_messages')->with($data) : $data;
    }

    public function doDeletePositions()
    {
        return view('front::History.do_delete');
    }

    public function deletePositions()
    {
        HistoryModalHelper::deletePositions();

        return HistoryModalHelper::deletePositions();
    }

    public function getPosition()
    {
        return HistoryModalHelper::getPosition();
    }

    public function getFiltered()
    {
        return HistoryModalHelper::getFiltered();
    }
}