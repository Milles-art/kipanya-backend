<?php
namespace App\Http\Controllers\Admin\Wear;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class EnquiryController extends Controller
{
    private function authorize(Request $request): void { abort_unless($request->user()?->hasPermission('commerce.manage'), 403); }
    public function index(Request $request): View
    {
        $this->authorize($request);
        $messages=ContactMessage::query()
            ->when($request->filled('q'),function($q)use($request){$term=trim((string)$request->string('q'));$q->where(fn($q)=>$q->where('name','like',"%{$term}%")->orWhere('email','like',"%{$term}%")->orWhere('message','like',"%{$term}%"));})
            ->when($request->filled('status'),fn($q)=>$q->where('status',$request->string('status')))
            ->when($request->filled('type'),fn($q)=>$q->where('type',$request->string('type')))
            ->latest()->paginate(25)->withQueryString();
        return view('admin.wear.enquiries.index',compact('messages'));
    }
    public function show(Request $request, ContactMessage $message): View { $this->authorize($request); return view('admin.wear.enquiries.show',['message'=>$message]); }
    public function updateStatus(Request $request, ContactMessage $message): RedirectResponse
    {
        $this->authorize($request);
        $data=$request->validate(['status'=>['required',Rule::in(['new','read','in_progress','resolved','closed'])]]);
        $before=$message->status; $message->update(['status'=>$data['status']]);
        app(\App\Support\AuditLogger::class)->log($request,'admin.wear.enquiry.status_updated',$message,['before'=>$before,'after'=>$message->status]);
        return back()->with('success','Enquiry status updated.');
    }
}
