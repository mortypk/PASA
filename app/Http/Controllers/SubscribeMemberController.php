<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Corcel\Model\User;
use App\Helpers\PasswordHash;
use App\Helpers\Helper;

use App\Helpers\Hele;
use App\DataTables\MembersDataTable;
use App\Http\Requests\MemberRequest;
use App\Mail\ApprovalEmail;
use App\Models\AdditionalMemberInfos;
use App\Models\MembershipStatus;
use App\Models\MembershipType;
use App\Models\States;
use App\Models\Subscription;
use App\Models\Title;
use App\Models\MembersContact;
use App\Models\MembersAddress;
use App\Models\User as ModelsUser;
use App\Models\VolunteerDetail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreMemberRequest;
use App\Models\SubscriptionPlan;
use App\Models\MemberPedigree;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\Else_;
use Illuminate\Support\Facades\Hash;

class SubscribeMemberController extends Controller
{
    public function __construct()
    {
    }
    public function create()
    {
        $data['titles'] = Title::all();
        $data['states'] = States::all();
        $data['membership_status'] = MembershipStatus::all();
        //$data['membership_types'] = MembershipType::all();
        $data['membership_types'] = SubscriptionPlan::all();

        return view('page.members.create', compact('data'));
    }

    public function index(Request $request)
    {
        //$members = Member::with('membershipType', 'membershipStatus', 'additionalInfo')->get();
        $members = Member::with('membershipType', 'membershipStatus', 'additionalInfo')->get();

        $membershipTypeOptions = MembershipType::pluck('name')->toArray();
        array_unshift($membershipTypeOptions, '');

        $membershipStatusOptions = MembershipStatus::pluck('name')->toArray();
        array_unshift($membershipStatusOptions, '');

        return view('page.members.index', compact('members', 'membershipTypeOptions', 'membershipStatusOptions'));
    }

    public function store(StoreMemberRequest $request)
    {
        try {
            $member = Member::create([
                'title_id' => $request->title,
                'title_detail' => $request->title_detail,
                'family_name' => $request->family_name,
                'given_name' => $request->given_name,
                'preferred_name' => $request->preferred_name,
                'initials' => $request->initials,
                'post_nominal' => $request->post_nominal ?? null,
                //'date_of_birth' => !empty($request->date_of_birth) ? date('Y-m-d', strtotime($request->date_of_birth)) : null,
                'date_of_birth' => $request->date_of_birth, //&& is_numeric($request->date_of_birth) && $request->date_of_birth >= 00 && $request->date_of_birth <= 31) ? (int)$request->date_of_birth : null,
                'month_of_birth' => $request->month_of_birth, //&& is_numeric($request->month_of_birth) && $request->month_of_birth >= 01 && $request->month_of_birth <= 12) ? (int)$request->month_of_birth : null,
                'year_of_birth' => $request->year_of_birth, //&& is_numeric($request->year_of_birth) && $request->year_of_birth >= 1900 && $request->year_of_birth <= 2100) ? (int)$request->year_of_birth : null,
                'username' => $request->username,
                'member_type_id' => $request->member_type_id,
                'member_status_id' => $request->member_status_id,
                'journal' => $request->journal,
            ]);

            $member->address()->updateOrCreate(['member_id' => $member->id], [
                //'unit_no' => $request->unit_no,
                'number_street' => $request->number_street,
                'suburb' => $request->city_id,
                'state_id' => $request->county_id,
                'country_id' => $request->country_id,
                'post_code' => $request->post_code,
            ]);
            $member->contact()->updateOrCreate(['member_id' => $member->id], [
                'email' => $request->email,
                'mobile' => $request->mobile,
                'phone' => $request->phone,
                'area_code' => $request->area_code,
            ]);

            AdditionalMemberInfos::updateOrCreate(['member_id' => $member->id], [
                'member_id' => $member->id,
                'membership_number' => $request->membership_number,
                'general_notes' => $request->general_notes,
                'end_status_notes' => $request->end_status_notes,
                'partner_member' => (int)$request->partner_member,
                'volunteer' => (int)$request->volunteer,
                'volunteer_skills_working' => $request->volunteer_skills_working,
                'registration_form_received' => (int)$request->registration_form_received,
                'signed_agreement' => (int)$request->signed_agreement,
                'key_holder' => (int)$request->key_holder,
                'key_held' => $request->key_held,
                'date_membership_end' => !empty($request->date_membership_end) ? date('Y-m-d', strtotime($request->date_membership_end)) : null,

                //'date_membership_approved' => !empty($request->date_membership_approved) ? date('Y-m-d', strtotime($request->date_membership_approved)) : null
            ]);
            $volunteerEnable = AdditionalMemberInfos::where('member_id', $member->id)->first();
            if ($volunteerEnable && $volunteerEnable->volunteer == 1) {
                VolunteerDetail::updateOrCreate(['member_id' => $member->id], [
                    'member_id' => $member->id,
                    'experience' => $request->experience,
                    'health_issues' => $request->health_issues,
                    'contact' => $request->contact,
                    'skills' => $request->skills,
                    'availability' => $request->availability
                ]);
            }
            return response()->json([
                "status" => true,
                "message" => "Member Created Successfully",
                "redirectTo" => route("members.view-member", ['id' => $member->id])
            ]);
        } catch (\Exception $e) {
            return response()->json(["status" => false, "message" => $e->getMessage()]);
        }
    }

    public function subscribe(MemberRequest $request)
    {
        Member::create($request->validated());
        return response()->json(["status" => true, "message" => "Member created successfully"]);
    }

    public function viewPedigree($id)
    {
        $member = Member::find($id);
        $data['state_name'] = Helper::getState($member?->state);
        $data['gender_name'] = Helper::getGender($member?->ancestor?->gender);
        $data['place_of_arrival'] = Helper::getPlaceOfArrival($member?->ancestor?->place_of_arrival);
        $data['name_of_the_ship'] = Helper::getNameofShip($member?->ancestor?->name_of_the_ship);

        return view('page.members.view-pedigree', compact('member', 'data'));
    }

    public function viewMember($id)
    {
        $member = Member::find($id);

        $data['state_name'] = Helper::getState($member?->address?->state);
        $data['gender_name'] = Helper::getGender($member?->ancestor?->gender);
        $data['place_of_arrival'] = Helper::getPlaceOfArrival($member?->ancestor?->place_of_arrival);
        $data['name_of_the_ship'] = Helper::getNameofShip($member?->ancestor?->name_of_the_ship);
        $data['membership_types'] = SubscriptionPlan::all();
        //$data['membership_types'] = MembershipType::all();
        $data['membership_status'] = MembershipStatus::all();

        return view('page.members.view-member', compact('member', 'data'));
    }

    public function editMember($id)
    {
        $member = Member::find($id);
        $data['titles'] = Title::all();
        $data['state_name'] = Helper::getState($member?->address?->state);
        $data['states'] = States::all();
        $data['membership_status'] = MembershipStatus::all();
        //$data['membership_types'] = MembershipType::all();
        $data['membership_types'] = SubscriptionPlan::all();
        $data['gender_name'] = Helper::getGender($member?->ancestor?->gender);
        $data['place_of_arrival'] = Helper::getPlaceOfArrival($member?->ancestor?->place_of_arrival);
        $data['name_of_the_ship'] = Helper::getNameofShip($member?->ancestor?->name_of_the_ship);

        return view('page.members.edit-member', compact('member', 'data'));
    }

    public function memberDetailUpdate(Request $request, $id)
    {
        $needToValidate = [
            'title' => 'required',
            'family_name' => 'required',
            'given_name' => 'required',
            'preferred_name' => 'nullable',
            'date_of_birth' => 'nullable',
            'month_of_birth' => 'nullable',
            'year_of_birth' => 'nullable',

            'number_street' => 'nullable',
            'suburb' => 'nullable',
            'state' => 'nullable',
            'country' => 'nullable',
            'post_code' => 'nullable',

            'phone' => 'nullable',
            'mobile' => 'nullable',
            'journal' => 'required',

        ];

        $validator = Validator::make($request->all(), $needToValidate);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(), "values" => $request->all()], 422);
        }

        $member = Member::find($id);
        $member->title_id = $request->title;
        $member->title_detail = $request->title_detail;
        $member->family_name = $request->family_name;
        $member->given_name = $request->given_name;
        $member->preferred_name = $request->preferred_name;
        $member->initials = $request->initials;
        $member->post_nominal = $request->post_nominal ?? NULL;
        //$member->date_of_birth = !empty($request->date_of_birth) ? date('Y-m-d', strtotime($request->date_of_birth)) : null;
        $member->date_of_birth = $request->date_of_birth; //&& is_numeric($request->date_of_birth) && $request->date_of_birth >= 00 && $request->date_of_birth <= 31) ? (int)$request->date_of_birth : null,
        $member->month_of_birth = $request->month_of_birth; //&& is_numeric($request->month_of_birth) && $request->month_of_birth >= 01 && $request->month_of_birth <= 12) ? (int)$request->month_of_birth : null,
        $member->year_of_birth = $request->year_of_birth; //&& is_numeric($request->year_of_birth) && $request->year_of_birth >= 1900 && $request->year_of_birth <= 2100) ? (int)$request->year_of_birth : null,

        //$member->username = $request->username;
        $member->member_type_id = $request->member_type_id;
        $member->member_status_id = $request->member_status_id;
        $member->journal = $request->journal;

        $member->save();

        $member->address()->updateOrCreate([], [
            'unit_no' => $request->unit_no,
            'number_street' => $request->number_street,
            'suburb' => $request->city_id,
            'state_id' => $request->county_id,
            'country_id' => $request->country_id,
            'post_code' => $request->post_code,
        ]);


        $member->contact()->updateOrCreate([], [
            ///'email' => $request->email,
            'mobile' => $request->mobile,
            'phone' => $request->phone,
            'area_code' => $request->area_code,
        ]);


        AdditionalMemberInfos::updateOrCreate(['member_id' => $member->id], [
            'member_id' => $member->id,
            'membership_number' => $request->membership_number,
            'general_notes' => $request->general_notes,
            'end_status_notes' => $request->end_status_notes,
            'partner_member' => (int)$request->partner_member,
            'volunteer' => (int)$request->volunteer,
            'volunteer_skills_working' => $request->volunteer_skills_working,
            'registration_form_received' => (int)$request->registration_form_received,
            'signed_agreement' => (int)$request->signed_agreement,
            'key_holder' => (int)$request->key_holder,
            'key_held' => $request->key_held,
            'date_membership_end' => !empty($request->date_membership_end) ? date('Y-m-d', strtotime($request->date_membership_end)) : null,
            'date_membership_approved' => !empty($request->date_membership_approved) ? date('Y-m-d', strtotime($request->date_membership_approved)) : null
        ]);
        $volunteerEnable = AdditionalMemberInfos::where('member_id', $member->id)->first();
        if ($volunteerEnable && $volunteerEnable->volunteer == 1) {
            VolunteerDetail::updateOrCreate(['member_id' => $member->id], [
                'member_id' => $member->id,
                'experience' => $request->experience,
                'health_issues' => $request->health_issues,
                'contact' => $request->contact,
                'skills' => $request->skills,
                'availability' => $request->availability
            ]);
        }

        if (Auth::user()->name == 'Admin') {
            return response()->json([
                "status" => true,
                "message" => "Member Updated successfully",
                "redirectTo" => route("members.view-member", ['id' => $member->id])
            ]);
        } else {
            return response()->json([
                "status" => true,
                "message" => "Member Updated successfully",
                "redirectTo" => route("profile")
            ]);
        }
    }

    public function update(Member $member)
    {
        try {
            DB::beginTransaction();

            AdditionalMemberInfos::updateOrCreate(
                ['member_id' => $member->id],
                [
                    'date_membership_approved' => now(),
                    'date_membership_end' => now()->addYear(),
                ]
            );

            $usr = ModelsUser::create([
                "email" => $member->contact->email,
                "password" => Hash::make($member->password), // Hash the password
                "name" => $member->given_name . " " . $member->family_name
            ]);

            $usr->assignRole("user");
            Mail::to($member->contact->email)->send(new ApprovalEmail($member));

            DB::commit();

            return response()->json([
                "status" => true,
                "message" => "Member Approved Successfully",
                "redirectTo" => route("members.view-member", ['id' => $member->id])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Log the exception for debugging purposes
            Log::error('Error updating member: ' . $e->getMessage());

            return response()->json([
                "status" => false,
                "message" => "Error updating member",
                "error" => $e->getMessage(),
            ]);
        }
    }

    public function editPedigree($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return redirect()->route('members.index')->with('error', 'Member not found.');
        }

        return view('page.members.edit-pedigree', ['member' => $member]);
    }

    public function updatePedigree(Request $request, $id)
    {
        $this->validate($request, [
            'pedigree.*.f_name' => 'nullable|string|max:255',
            'pedigree.*.m_name' => 'nullable|string|max:255',
            // Add more validation rules as needed
        ]);

        $member = Member::findOrFail($id);

        $existingPedigreeIds = MemberPedigree::where('member_id', $member->id)->pluck('id')->toArray();

        $updatedPedigreeIds = [];

        foreach ($request->pedigree as $pedigreeData) {
            $pedigreeId = $pedigreeData['id'] ?? null;
            $pedigree = $pedigreeId ? MemberPedigree::find($pedigreeId) : new MemberPedigree();

            if ($pedigree) {
                $pedigree->fill($pedigreeData);
                $pedigree->member_id = $member->id;
                $pedigree->save();

                $updatedPedigreeIds[] = $pedigree->id;
            }
        }

        // Delete any pedigrees that are not in the updated list
        MemberPedigree::where('member_id', $member->id)
            ->whereNotIn('id', $updatedPedigreeIds)
            ->delete();
        if (Auth::user()->name == 'Admin') {
            return response()->json([
                "status" => true,
                "message" => "Pedigree updated successfully",
                "redirectTo" => route("members.view-pedigree", ['id' => $member->id])
            ]);
        } else {
            return response()->json([
                "status" => true,
                "message" => "Pedigree updated successfully",
                "redirectTo" => route("profile")
            ]);
        }
    }

    public function addPedigree($id)
    {
        $member = Member::find($id);

        if (!$member) {
            return redirect()->route('members.index')->with('error', 'Member not found.');
        }

        return view('page.members.add-pedigree', ['member' => $member]);
    }

    public function storePedigree(Request $request, $memberId)
    {
        $this->validate($request, [
            'pedigrees.*.f_name' => 'nullable|string|max:255',
            'pedigrees.*.m_name' => 'nullable|string|max:255',
        ]);

        $member = Member::findOrFail($memberId);

        foreach ($request->pedigrees as $pedigreeData) {
            $pedigreeData['member_id'] = $memberId;
            MemberPedigree::create($pedigreeData);
        }
        return response()->json([
            "status" => true,
            "message" => "Pedigree Added successfully",
            "redirectTo" => route("members.view-pedigree", ['id' => $member->id])
        ]);
    }
}
