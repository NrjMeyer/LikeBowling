<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Frame;
use App\Models\Lance;

class LanceController extends Controller
{
    public function show(): View
    {
        $score = 0;
        $frames = Frame::all();

        foreach ($frames as $frame){
            switch ($frame->type){
                case 'normal':
                    $score += $frame->score;
                    break;
                case 'spare':
                    $score += $frame->score;
                    $score += $this->check_spare($frame->number);
                    break;
                case 'full':
                    $score += $frame->score;
                    $score += $this->check_full($frame->number);
                    break;
            }
        }
        $scores = [['','',''],['','',''],['','',''],['','',''],['','','','']];

        foreach ($frames as $frame){
            switch ($frame->type){
                case 'full':
                    $scores[$frame->number - 1] = ['X','',''];
                    break;
                case 'normal':
                    $scores[$frame->number - 1] = [$frame->lances->where('number',1)->first()->down,$frame->lances->where('number',2)->first()->down,$frame->lances->where('number',3)->first()->down];
                    break;
                case 'spare':
                    $count = count($frame->lances);
                    if($count == 2) $scores[$frame->number - 1] = [$frame->lances->where('number',1)->first()->down,'/',''];
                    if($count == 3) $scores[$frame->number - 1] = [$frame->lances->where('number',1)->first()->down,$frame->lances->where('number',2)->first()->down,'/'];
                    break;
            }
        }

        return view('welcome',[
            'score'=>$score,
            'scores'=>$scores,
        ]);
    }

    public function store(Request $request)
    {
        $frame = Frame::where('number',$request->frame_number)->first();
        $lance = Lance::where('number',$request->lance_number)->where('frame_id',$request->frame_number)->first();
        if( ($request->frame_number < 5) && ($request->lance_number >3) ){
            return redirect('/')->with('status', 'Invalid data, nombre de lancé > 3 ');
        }
        if(empty($old_lance)){
            $lance = new Lance;
        }
        if(empty($frame)){
            $frame = new Frame;
            $frame->number = $request->frame_number;
            $frame->score = 0;
            $frame->type = $request->quilles == 15 ? 'full' : 'normal';
            $frame->score = $request->quilles;

        }else{
            $new_score = $frame->score + $request->quilles;
            if(( $new_score > 15) && ($request->frame_number < 5) ){
                return redirect('/')->with('status', 'Invalid data, (score of frame > 15)');
            }elseif($new_score == 15){
                $frame->type = 'spare';
                $frame->score = $new_score;
            }else{
                $frame->score = $new_score;
            }
        }
        $lance->number = $request->lance_number;
        $lance->frame_id = $request->frame_number;
        $lance->down = $request->quilles;

        $lance->save();
        $frame->save();


        return to_route('home');
    }

    public function reset(Request $request){
        Frame::truncate();
        Lance::truncate();
        return to_route('home');
    }

    private function check_spare($number){
        $i = 0;
        $score = 0;
        $lances = Lance::where('frame_id', '>', $number)->orderBy('frame_id', 'asc')->orderBy('number', 'asc')->get();
        foreach ($lances as $lance){
            if($i == 2) break;
            $score += $lance->down;
            $i++;
        }
        return $score;
    }

    private function check_full($number){
        $i = 0;
        $score = 0;
        $lances = Lance::where('frame_id', '>', $number)->orderBy('frame_id', 'asc')->orderBy('number', 'asc')->get();
        foreach ($lances as $lance){
            if($i == 3) break;
            $score += $lance->down;
            $i++;
        }
        return $score;
    }
}
