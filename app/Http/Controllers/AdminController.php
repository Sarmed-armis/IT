<?php

namespace App\Http\Controllers;

use App\Models\BillDetails;
use App\Models\BillOfLanding;
use App\Models\Branch;
use App\Models\BusinessInvites;
use App\Models\Client;
use App\Models\Port;
use App\Models\Setting;
use App\Models\Ship;
use App\Models\Trip;
use App\Models\User;
use http\Header;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    // AddClient

    public function  Settings()
    {


        $settingsCount=0;
        $settingsLast=Array();

        if (Setting::get()->count()>0){
            $settingsCount=Setting::get()->count();
            $settingsLast=Setting::get()->Last();

        }


        $InfromationArray=Array(
            "settingsCount" =>$settingsCount,
            "settingsLast"=>$settingsLast
        );




        return view('AdminPages.Index',$InfromationArray);


    }
    public function AddUser()
    {


        $Branches=Branch::all();
        $CurrentUsers=User::where('bar_id','=',auth()->user()->bar_id)->get();


        $InformationArray=array(
            'roles'=>Role::all(),
            "Branches"=>$Branches,
            "CurrentUsers"=>$CurrentUsers
        );





        return view('AdminPages.AddUser',$InformationArray);


    }
    public function StoresUser(Request $request)
    {



        // Branch

       $User= User::create([
            'name' =>$request->EmpName,
            'email' =>$request->userName,
            'password' =>hash::make($request->userName),
            'firstLogin'=>1,
            'Branch'=>$request->Branch
        ]);



        DB::table('model_has_roles')->insert([
            'role_id' => $request->permission,
            'model_type' => 'App\Models\User',
            'model_id' => $User->id
        ]);


        return redirect()->back();



    }
    public function EditUser(Request $request){




        if ($request->userName!=null){




            $request->validate([
                'userName' => 'required|unique:users,email',
            ]);






        }





      //  dd($request->UserId);
        $HolderUser=User::find($request->UserId);

        $HolderUser->name=$request->EmpName==null?$HolderUser->name:$request->EmpName;
        $HolderUser->email=$request->userName==null?$HolderUser->email:$request->userName;
        $HolderUser->password=$request->password==null?$HolderUser->password:hash::make($request->password);
        $HolderUser->save();

        Session::flash('message',"???? ?????????? ?????????????? ??????????");

        return redirect()->back();

    }
    public function DeleteUser(Request $request){
        $HolderUser=User::find($request->UserId);
       $HolderUser->delete();

        //User::softDeleted($HolderUser->id);


        Session::flash('message',"???? ??????????  ??????????");

        return redirect()->back();


    }
    public function StoresSetting(Request $request)
    {


// Tripoli#2026



      //  dd($request); v usdToCustomer

        Setting::create([
               'UroPrice'      =>$request["UroPrice"]
            , 'UroToCustomer'  =>$request["UroToCustomer"]
            , 'usdPrice'       =>$request["usdPrice"]
            , 'usdToCustomer'       =>$request["usdToCustomer"]
            , 'penaltyFessCost'  =>$request["penaltyFessCost"]
            , 'HandOverFessCost'  =>$request["HandOverFessCost"]
            , 'ContainerTwentyKg' =>$request["ContainerTwentyKg"]
            , 'ContainerFortyKg' =>$request["ContainerFortyKg"]
            , 'ContainerFortyKgUpToThirtyOneDay' =>$request["ContainerFortyKgUpToThirtyOneDay"]
            , 'ContainerFortyKgGraterThanThirtyOneDay' =>$request["ContainerFortyKgGraterThanThirtyOneDay"]
            , 'ContainerTwentyKgUpToThanThirtyOneDay' =>$request["ContainerTwentyKgUpToThanThirtyOneDay"]
            , 'ContainerTwentyKgGraterThanThirtyOneDay' =>$request["ContainerTwentyKgGraterThanThirtyOneDay"]
            , 'CompanyBalance' =>$request["CompanyBalance"]

        ]);


        return redirect()->back();


    }
    public function TripManagement()
    {




        $Branches=Branch::all();
        $PortsIn=Port::where('type','=','1')->get();
        $PortsOut=Port::where('type','=','2')->get();
        $Trips=Trip::where('bar_id','=',auth()->user()->bar_id)->get();
        $Clients=Client::all();







        // this is Array pushed to the UI
        $InformationArray=Array(
            "TotalContainer"=>0,
            "PortsIn"=>$PortsIn,
            "PortsOut"=>$PortsOut,
            "Branches"=>$Branches,
            "Trips"=>$Trips,
            "Clients"=>$Clients
        );

        return view('AdminPages.TripManagement',$InformationArray);

    }
    public function StoresShips(Request $request)
    {
        // this is for store NewShips

      //  dd($request);


        Trip::create([
             'shipName' =>  $request->ShipName
            , 'TripNumber' =>  $request->ShipNumber
            , 'port_in' =>  $request->PortIn
            , 'port_out'=>  $request->PortOut
            , 'ArriveDate'=>  $request->ArriveDate
            , 'EraseDate'=>  $request->EraseDate
             ,'bar_id'=>auth()->user()->bar_id
        ]);



        return redirect()->back();




    }
    public function AddClient()
    {

     //   AddClient
        $Branches=Branch::all();
        $CurrentClients=Client::all();


        $InformationArray=array(
            'roles'=>Role::all(),
            "Branches"=>$Branches,
            "CurrentClients"=>$CurrentClients
        );





        return view('AdminPages.AddClient',$InformationArray);


    }
    public function StoresClient(Request $request){



        $request->validate([
            'ClientEmail' => 'required|email',
            'ClientName' => 'required|string',
            'ClientPone' => 'required|numeric',
        ]);


        Client::create([
                'name'=> $request->ClientName
            , 'email' => $request->ClientEmail
            , 'phoneCall' => $request->ClientPone
        ]);

        Session::flash('message',"???? ?????????? ???????????? ??????????");

        return redirect()->back();


    }
    public function StoreNewBill(Request $request){

 //        'trip_id', 'shippers', 'order_id', 'BillOfLading', 'bar_id'

        $request->validate([
            'Client' => 'required|string',
            'BillOfLading' => 'required|string',
            'TripId' => 'required|string',
            'TotalContainer' => 'required|numeric',
        ]);


        BillOfLanding::create([
            'shippers'=> $request->shippers==null?"N'A":$request->shippers
            , 'order_id' => $request->Client
            , 'trip_id' => $request->TripId
            , 'BillOfLading' => $request->BillOfLading
            , 'TotalContainer' => $request->TotalContainer
            ,'bar_id'  =>auth()->user()->bar_id
        ]);

        Session::flash('message',"???? ?????????? ???????????????? ??????????");

        return redirect()->back();





    }
    public function GetBillsByTripId(Request $request)
    {


        $Bills=BillOfLanding::where('trip_id','=',$request->TripId)->get();




        $FinshedBillIdArray=Array();
        foreach ($Bills as $Bill)
        {

            if(BillDetails::where('bill_id','=',$Bill->id)->count()>=$Bill->TotalContainer){


                array_push($FinshedBillIdArray,$Bill->id);

             //   dd($FinshedBillIdArray);
            }
        }



        $RemainBills=BillOfLanding::whereNotIn('id',$FinshedBillIdArray)->where('trip_id','=',$request->TripId)->select('id','BillOfLading as text')->get();
      //dd($RemainBills);

        return response()->json(["results"=>$RemainBills]);



    }
    public function TripManagementFillBill(Request $request)
    {

        $Branches=Branch::all();
        $PortsIn=Port::where('type','=','1')->get();
        $PortsOut=Port::where('type','=','2')->get();
        $Trips=Trip::where('bar_id','=',auth()->user()->bar_id)->get();
        $Clients=Client::all();

        if($request->id==null){

            return redirect('/TripManagement');
        }
        $theBillOfLanding=BillOfLanding::find($request->id);








        // this is Array pushed to the UI
        $InformationArray=Array(
            "TotalContainer"=>$theBillOfLanding->TotalContainer,
            "bill_code"=>$theBillOfLanding->BillOfLading,
            "bill_id"=>$theBillOfLanding->id,
            "PortsIn"=>$PortsIn,
            "PortsOut"=>$PortsOut,
            "Branches"=>$Branches,
            "Trips"=>$Trips,
            "Clients"=>$Clients
        );

        return view('AdminPages.TripManagement',$InformationArray);





       // dd($request->id);



    }
    public function StoreBillContent(Request $request)
    {


        $Count=count($request->containerNumber);


        for($i=0;$i<$Count;$i++)
        {
            BillDetails::create([

                  'bill_id'  =>$request->bill_code
                , 'containerDescription' =>$request->containerDescription[$i]
                , 'containerNumber' =>$request->containerNumber[$i]
                , 'containerType_id' =>$request->containerType_id[$i]
                , 'IsColdContainer' =>$request->IsColdContainer[$i]
                , 'Size' =>$request->Size[$i]
                , 'weight' =>$request->weight[$i]
            ]);
        }




        $Messege=" ???????????? ??????????".$Count."???? ??????????  ";

        Session::flash('message',$Messege);

        return redirect('/TripManagement');






    }

    public function B2BInvites()
    {


        //   AddClient
        $Branches=Branch::all();
        $CurrentClients=BusinessInvites::all();


        $InformationArray=array(
            'roles'=>Role::all(),
            "Branches"=>$Branches,
            "CurrentClients"=>$CurrentClients
        );





        return view('AdminPages.B2BInvites',$InformationArray);


    }


    public function ReadFile(){


        $i=0;

        $FileName='Io_clifor_detcon.dat';
        $personalinfo = file("DatFile/".$FileName);


       //dd($personalinfo);

        $Header=array();

        $Header=explode('|',$personalinfo[0]);

      //  dd($Header);
        foreach ($personalinfo as $personalinf){
            $Item=explode('|',$personalinf);
           // dd($Item);


            if ($i>0)
            {


                BusinessInvites::create([
                    'COGNOME_NOME'    => $Item[7]
                    ,'NOME'           => $Item[8]
                    ,'EMAIL1'        =>  $Item[20]
                ]);
            }




            $i=$i+1;



        }





    }


}
