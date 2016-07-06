<p><em>This is a copy of the message we received from you.</em></p>
<p><strong>Transaction ID:</strong>{{$tid}}</p>
<p> <strong>Contact Topic:</strong>{{$lbl}}</p>
<p><strong>Name:</strong>{{$nm}}<p/>
<p> <strong>Email:</strong>{{$email}}<p/>
    @if($uid!="")
       <p> <strong>University ID:</strong> -redacted- </p>
    @endif
    @if($snm!="")
       <p> <strong>Student Name:</strong> -redacted- </p>
    @endif
@if($ouid!="")
    <p> <strong>Student ID:</strong> -redacted- </p>
@endif
@if($upload_id!='')
<p><strong>Attachments*:</strong>Yes</p>
@endif
<p><strong>Message Content:</strong></p><p>{{$text}}</p>
<p><strong>This message was sent from an unmonitored account.
        Please do not reply.</strong></p>
@if($upload_id!='')
    <p>[*] Attachments are not included in this message in order to protect your sensitive data.</p>
@endif

@if((date("m/d") >= '08/01') && (date("m/d") <= '08/31')) {
<p>PLEASE NOTE: Some 3rd party payers are receiving an error message that can sometimes be resolved by <a href="https://kb.iu.edu/d/ahic">clearing the cache</a> or switching browsers.</p>
<p>Thank you for Contacting Student Central on Union. Although our expanded team is working overtime to respond to thousands of inbound calls and hundreds of emails each day, we understand response times are often longer than expected at this time of year. Avoid the wait by finding <a href="http://studentcentral.indiana.edu/register/mail/big-ten/index.shtml">answers to the most commonly asked questions</a> online.</p>
@endif
