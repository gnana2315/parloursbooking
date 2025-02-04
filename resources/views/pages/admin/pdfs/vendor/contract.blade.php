<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Parloursbooking.com | Vendors Contract</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ URL::asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ URL::asset('admin/css/adminlte.css') }}">
  <style>
    ul{list-style-type:none;}
  </style>
</head>
<body>
<div class="wrapper">
  <!-- Main content -->
  <section class="invoice">
    <!-- title row -->
    <div class="row">
      <table border="0">
        <tr>
          <th><img src="{{ public_path('images/parlours-booking-logob.png') }}" alt="PB Logo" width="72px" class="brand-image img-circle"></th>
          <th align="left">
            <h2 class="page-header">
              <span class="brand-text font-weight-light">Parlours Booking Vendor Contract</span>
            </h2>
          </th>
        </tr>
      </table>
    </div>
    <!-- info row -->
    <div class="row invoice-info">
      <table border="0" style="width:100%;">
        <tr>
          <th style="width:50%; text-align:left;">            
            From
          </th>
          <th style="width:50%; text-align:right;">
            To
          </th>
        </tr>
        <tr>
          <td>
            <address>
              <strong>Parlours Booking PVT LTD</strong><br>
              No:40, Vivekananda Road, Wellawatta<br>
              Colombo 06, Sri Lanka<br>
              Phone: (+94) 77 225 8864<br>
              Email: info@parloursbooking.com
            </address>
          </td>          
          <td style="text-align:right;">
            <address>
              <strong>{{$vendor_name}}</strong><br>
              {{$vendor_person_name}}<br>
              {{$vendor_address}}<br>
              Phone: {{$vendor_contactno}} | {{$vendor_person_contactno}}<br>
              Email: {{$vendor_email}}
            </address>
          </td>
        </tr>
      </table>
      <br>
      <!-- /.col -->
      <div class="col-sm-4 invoice-col">
        <b style="text-align:center!important;"><u>Terms and Conditions for Vendors</u></b>
        <br>
        <br>
        <b>Effective Date: {{ $date }}</b>
        <br>
        <br>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
    <div class="row">
      <div class="col-12">
        This Terms and Conditions Agreement ("Agreement") is entered into by and between <b>Parlours Booking PVT LTD</b> registered at [No:40, Vivekananda Road, Wellawatta, Colombo 06, Sri Lanka], and you, the vendor 
        <u><b>{{$vendor_name}}</b> registered at [{{$vendor_address}}]</u>, 
        who has agreed to provide services through the ParloursBooking platform. By accessing, registering, or using the services of ParloursBooking, you agree to be bound by these Terms and Conditions.
        <br><br>
        <ol>
          <li>
            Scope of Agreement
            <ul>
              <li>1.1 <b>Platform Services: </b>ParloursBooking operates a platform that allows vendors (e.g., beauty professionals, salons, or other service providers) to list and offer their services to customers for booking appointments online.<li>
              <li>1.2 <b>Vendor’s Responsibilities: </b>As a Vendor, you are responsible for managing your service offerings, availability, pricing, and ensuring that all services comply with applicable laws and regulations. You must maintain accurate and current information on the platform.<li>
              <li>1.3 <b>Agreement Responsibilities: </b>This Agreement begins on the date you accept these Terms and Conditions and will continue until terminated as provided herein.<li>
            </ul>
          </li>
          <li>
            Vendor Registration and Account
            <ul>
              <li>2.1 <b>Account Creation: </b>To list your services on the ParloursBooking platform, you must create an account by providing accurate and complete information, including but not limited to your business name, services offered, pricing, contact information, and payment details.</li>
              <li>2.2 <b>Account Security: </b>You agree to maintain the confidentiality of your login credentials and to notify us immediately if you suspect any unauthorized access to your account.</li>
              <li>2.3 <b>Eligibility: </b>You represent and warrant that you are legally authorized to offer the services you list on the Platform and that you will comply with all relevant licensing, regulatory, and legal requirements.</li>
            </ul>
          </li>
          <li>
            Vendor Obligations
            <ul>
              <li>3.1 <b>Service Quality: </b>You agree to provide high-quality services in a professional manner and ensure customer satisfaction. You shall handle any complaints, disputes, or issues with customers promptly.</li>
              <li>3.2 <b>Compliance with Laws: </b>You agree to comply with all applicable laws, regulations, and industry standards in connection with the services you provide through the Platform. This includes, but is not limited to, health and safety regulations, labor laws, and consumer protection laws.</li>
              <li>3.3 <b>Scheduling & Cancellations: </b>You are responsible for updating your availability and honoring all appointments scheduled through the Platform. In case of cancellations or rescheduling, you must notify affected customers in a timely and transparent manner.</li>
            </ul>
          </li>
          <li>
            Platform’s Responsibilities
            <ul>
              <li>4.1 <b>Listing and Promotion: </b>ParloursBooking will provide a platform for you to list your services, set prices, and manage appointments. We may also assist in promoting your services through the Platform and marketing channels, but we do not guarantee any specific level of exposure, sales, or bookings.</li>
              <li>4.2 <b>Payment Processing: </b>ParloursBooking will facilitate the payment process for services rendered through the Platform. We may charge a fee for processing payments and any applicable transaction fees. The exact terms will be outlined in the "Payment Terms" section.</li>
              <li>4.3 <b>Customer Interaction: </b>While we facilitate communication between you and your customers, we are not responsible for any interactions, agreements, or disputes between you and your customers.</li>
            </ul>
          </li>
          <li>
            Fees and Payment Terms
            <ul>
              <li>5.1 <b>Service Fees: </b>ParloursBooking may charge vendors a commission for using the Platform. The applicable fees will be specified in your Vendor Account and may change from time to time.</li>
              <li>5.2 <b>Payment Terms: </b>Payments for services rendered through the Platform will be processed by ParloursBooking and transferred to your designated account after deducting any applicable fees. Payment schedules will be outlined in your account details.</li>
              <li>5.3 <b>Refunds and Disputes: </b>You are responsible for handling any refunds, chargebacks, or disputes that arise from transactions. ParloursBooking reserves the right to withhold payment in cases of fraud or unresolved customer complaints.</li>
            </ul>
          </li>
          <li>
            Intellectual Property
            <ul>
              <li>6.1 <b>Ownership of Content: </b>You retain ownership of any content you submit to the Platform, including images, descriptions, and service offerings. However, you grant ParloursBooking a worldwide, non-exclusive, royalty-free license to use, display, and promote this content on the Platform and associated marketing channels.</li>
              <li>6.2 <b>Trademark Usage: </b>You may not use any trademarks, logos, or branding of ParloursBooking without prior written consent. ParloursBooking retains ownership of all trademarks and intellectual property related to the Platform.</li>
            </ul>
          </li>
          <li>
            Confidentiality
            <ul>
              <li>7.1 <b>Confidential Information: </b>Both parties agree to keep confidential any proprietary or sensitive information obtained during the course of this Agreement, including but not limited to customer data, business strategies, and financial information.</li>
              <li>7.2 <b>Non-Disclosure: </b>You agree not to disclose any confidential information to third parties without the prior written consent of ParloursBooking, unless required by law.</li>
            </ul>
          </li>
          <li>
            Termination
            <ul>
              <li>8.1 <b>Termination by Vendor: </b>You may terminate this Agreement at any time by notifying ParloursBooking. Upon termination, your account will be deactivated, and you will no longer be able to offer services through the Platform.</li>
              <li>8.2 <b>Termination by ParloursBooking: </b>ParloursBooking reserves the right to suspend or terminate your account if you breach any of the terms in this Agreement, including failure to comply with laws, fraudulent activity, or non-payment of fees.</li>
              <li>8.3 <b>Effect of Termination: </b>Upon termination, you will be paid for any outstanding services rendered prior to termination, subject to any applicable fees or disputes. Termination does not affect any liability incurred prior to the termination date.</li>
            </ul>
          </li>
          <li>
            Limitation of Liability
            <ul>
              <li>9.1 <b>No Liability for Platform Performance: </b>ParloursBooking does not guarantee that the Platform will be error-free, uninterrupted, or free of viruses. We are not liable for any damages resulting from your use of the Platform.</li>
              <li>9.2 <b>Vendor’s Liability: </b>You agree to indemnify and hold ParloursBooking harmless from any claims, damages, or expenses arising from your services, actions, or failure to comply with the terms of this Agreement.</li>
            <ul>
          </li>
          <li>
            Miscellaneous
            <ul>
              <li>10.1 <b>Governing Law: </b>This Agreement shall be governed by and construed in accordance with the laws.</li>
              <li>10.2 <b>Dispute Resolution: </b>Any disputes arising under this Agreement shall be resolved through [arbitration/mediation] or in the courts of [Jurisdiction].</li>
              <li>10.3 <b>Amendments: </b>ParloursBooking reserves the right to modify or update these Terms and Conditions at any time. You will be notified of significant changes, and continued use of the Platform constitutes acceptance of the modified terms.</li>
              <li>10.4 <b>Severability: </b>If any provision of this Agreement is found to be invalid or unenforceable, the remainder of the Agreement shall remain in full force and effect.</li>
            </ul>
          </li>
          <li>
            Contact Information
            For any questions or concerns regarding these Terms and Conditions, please contact us at:
            <br><br>
            <b>ParloursBooking</b><br>
            Email: info@parloursbooking.com<br>
            Phone: (+94) 77 225 8864<br>
            Address: No:40, Vivekananda Road, Wellawatta, Colombo 06, Srilanka.<br><br>
          </li>
        </ol>
        By accepting these Terms and Conditions, you confirm that you have read, understood, and agree to comply with the terms set forth in this Agreement.<br><br>
        <ul>
          <li><b>Vendor Name: </b>{{$vendor_name}} | {{$vendor_person_name}}</li>
          <li><b>Vendor Signature: </b></li>
          <li><b>Date: </b></li>
          <li><b>Stamp : </b></li>
        </ul>
      </div>
    </div>

    <div style="page-break-after:always;"></div>
    
    <div class="row">
      <table border="0">
        <tr>
          <th><img src="{{ public_path('images/parlours-booking-logob.png') }}" alt="PB Logo" width="72px" class="brand-image img-circle"></th>
          <th align="left">
            <h2 class="page-header">
              <span class="brand-text font-weight-light">Parlours Booking Vendor Detail Achknowledgement</span>
            </h2>
          </th>
        </tr>
      </table>
    </div>

    <!-- Table row -->
    <div class="row">
      <div class="col-12 table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th><h2>Business Details</h2></th>
            </tr>
          </thead>
          <tbody style="text-align:left;">
            <tr>
              <td>Business ID: </td>
              <td>{{$vendor_id}}</td>
            </tr>
            <tr>
              <td>Vendor's BR No: </td>
              <td>{{$vendor_brno}} | {{$vendor_br_status ? 'Verified' : 'No'}}</td>
            </tr>
            <tr>
              <td>Business Name: </td>
              <td>{{$vendor_name}}</td>
            </tr>
            <tr>
              <td>Business Address: </td>
              <td>{{$vendor_address}}</td>
            </tr>
            <tr>
              <td>Business Contact No: </td>
              <td>{{$vendor_contactno}}</td>
            </tr>
            <tr>
              <td>Business Email: </td>
              <td>{{$vendor_email}}</td>
            </tr>
            <tr>
              <td>Business Logo: </td>
              <td>{{!empty($vendor_logo) ? 'Updated' : 'No'}}</td>
            </tr>
            <tr>
              <td>Business Certificate: </td>
              <td>{{ $vendor_parlourcertificate_status ? 'Verified' : 'No'}}</td>
            </tr>
            <tr>
              <td>Vendor Accept Term : </td>
              <td>{{!empty($vendor_accept_term_status) ? 'Verified' : 'No'}}</td>
            </tr>
          </tbody>
        </table>
        <table class="table table-striped">
          <thead>
            <tr>
              <th><h2>Vendor Person Details</h2></th>
            </tr>
          </thead>
          <tbody style="text-align:left;">
            <tr>
              <td>Name: </td>
              <td>{{$vendor_person_name}}</td>
            </tr>
            <tr>
              <td>Address: </td>
              <td>{{$vendor_person_address}}</td>
            </tr>
            <tr>
              <td>Contact No: </td>
              <td>{{$vendor_person_contactno}}</td>
            </tr>
            <tr>
              <td>Email: </td>
              <td>{{$vendor_person_email}}</td>
            </tr>
            <tr>
              <td>NIC: </td>
              <td>{{$vendor_nicno}} | {{ $vendor_nic_status ? 'Verified' : 'No'}}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- /.col -->
    </div>
    <!-- /.row -->
    <br><br>
    <div class="row">
      <div class="col-6">
        <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
        I hereby declare that the above-mentioned information is accurate to the best of my knowledge and belief.
        </p>
        <br><br>
        <ul>
          <li><b>Vendor Name: </b>{{$vendor_name}} | {{$vendor_person_name}}</li>
          <li><b>Vendor Signature: </b></li>
          <li><b>Date: </b></li>
          <li><b>Stamp : </b></li>
        </ul>
      </div>
    </div>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<!-- ./wrapper -->
<!-- Page specific script -->
<script>
  window.addEventListener("load", window.print());
</script>
</body>
</html>