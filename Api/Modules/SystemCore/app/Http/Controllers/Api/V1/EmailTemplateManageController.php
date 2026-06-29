<?php

namespace Modules\SystemCore\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\AdminEmailDetailsResource;
use App\Http\Resources\Admin\AdminEmailResource;
use App\Http\Resources\Com\PaginationResource;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\SystemCore\app\Models\EmailTemplate;

class EmailTemplateManageController extends Controller
{
    public function __construct(protected EmailTemplate $emailTemplate, protected Translation $translation)
    {

    }
    public function translationKeys(): mixed
    {
        return $this->emailTemplate->translationKeys;
    }

    public function allEmailTemplate(Request $request)
    {
        $query = EmailTemplate::query();

        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }

        if (!empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if (!empty($request->subject)) {
            $query->where('subject', 'like', '%' . $request->subject . '%');
        }
        $emailTemplates = $query->with('related_translations')->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => AdminEmailResource::collection($emailTemplates),
            'meta' => new PaginationResource($emailTemplates),
        ]);


    }

    public function addEmailTemplate(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:email_templates,name',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $body = strip_tags($request->body, '<p><a><strong><em><h1><h2><ul><ol><li><br>'); // Allow some tags

        EmailTemplate::create([
            'type' => $request->type,
            'name' => $request->name,
            'subject' => $request->subject,
            'body' => $body,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Email template added successfully',
        ], 201);
    }

    public function emailTemplateDetails(Request $request)
    {
        $validator = Validator::make(['id' => $request->id], [
            'id' => 'required|integer|exists:email_templates,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $email_template = EmailTemplate::find($request->id);
        if ($email_template) {
            return response()->json(new AdminEmailDetailsResource($email_template), 200);
        } else {
            return response()->json([
                'message' => __('data_not_found'),
            ], 404);
        }
    }

    public function editEmailTemplate(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:email_templates,id',
            'name' => [
                'required',
                'string',
                Rule::unique('email_templates', 'name')
                    ->ignore($request->id, 'id'),
            ],
            'subject' => 'sometimes|required|string',
            'body' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Find and update the email template
        $emailTemplate = EmailTemplate::findOrFail($request->id);
        createOrUpdateTranslation($request, $emailTemplate->id, 'Modules\SystemCore\app\Models\EmailTemplate', $this->translationKeys());
        $emailTemplate->update($request->only(['name', 'subject', 'body']));
        return response()->json([
            'message' => 'Email template updated successfully',
        ], 201);
    }


    public function deleteEmailTemplate(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make(['id' => $request->id], [
            'id' => 'required|exists:email_templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Find and delete the email template
        $emailTemplate = EmailTemplate::findOrFail($request->id);
        $emailTemplate->delete();

        return response()->json(['message' => 'Email template deleted successfully'], 200);
    }

    // Change Status of Email Template
    public function changeStatus(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:email_templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Find the email template by its ID
        $emailTemplate = EmailTemplate::find($request->id);

        if (!$emailTemplate) {
            return response()->json(['message' => 'Email template not found'], 404);
        }
        // Update the status
        $emailTemplate->status = !$emailTemplate->status;
        // Save the updated template
        $emailTemplate->save();

        return response()->json([
            'message' => 'Email template status updated successfully',
        ], 200);
    }

}
