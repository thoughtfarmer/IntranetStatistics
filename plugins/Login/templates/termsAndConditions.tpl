{literal}
<script type="text/javascript" src="libs/jquery/jquery.cookie.js"></script>
<script type="text/javascript">
function submitLoginForm() {
	var checked = $('#form_acceptterms').attr('checked');
	if (checked) {
		$.cookie("termsaccepted", 'true', { expires: 365 });
		return true;
	} else {
		$.cookie("termsaccepted", 'false');
		alert('Please accept the Terms and Conditions.');
		return false;
	}
}

function dontAcceptTerms() {
	$('#terms').hide();
	$('#login').show();
	$('#form_acceptterms').attr('checked', false);
}

function acceptTerms() {
	$('#terms').hide();
	$('#login').show();
	$('#form_acceptterms').attr('checked', true);
}

function showTerms() {
	$('#terms').show();
	$('#login').hide();
	return false;
}

$(document).ready(function() {

	// bind events
	$('#acceptButton').click(acceptTerms);
	$('#dontAcceptButton').click(dontAcceptTerms);	
	$('#termsLink').click(showTerms);		
	$('#login_form').submit(submitLoginForm);
	
	// check cookie and check the terms accepted checkbox
	var accepted =	$.cookie("termsaccepted");
	$('#form_acceptterms').attr('checked', accepted == 'true');	

});

</script>
<style type="text/css">
.termsButton {
    color: #224466;
	background-color: #CEE1EF !important;
	-moz-border-radius: 3px 3px 3px 3px;
    border: 1px solid #80B5D0;
    cursor: default;
    font-size: 13px;
    margin: 0;
    padding: 3px 5px;
    text-decoration: none;
}
.termsBoxOuter {
    background-color: #EAF3FA;
	-moz-border-radius: 5px 5px 5px 5px;
    font-weight: bold;
    padding: 16px 16px 10px;
	width:70%; 
	margin: 20px auto;
	display: none;
}
.termsBoxInner {
	background-color: white; 
	border: 1px solid black; 
	margin-top:15px;
	font-weight: normal;
	padding: 6px;
}
.termsButtonsContainer {
	text-align: right; 
	margin-top: 10px;
}
.acceptTermsCheckbox {
	font-weight:normal;
}
#acceptButton { 
	font-weight: bold;
	padding: 3px 18px; 
}
#terms p { margin-bottom: 18px; }
</style>
<div id="terms" class="termsBoxOuter">
<h1>Terms and Conditions</h1>
<h3>Please Read Carefully</h3>
<div class="termsBoxInner">
<p>THIS IS PART OF A LEGAL AND ENFORCEABLE CONTRACT
BETWEEN YOU AND OPENROAD COMMUNICATIONS INC. ("OPENROAD") BY
ACCESSING THIS SERVICE AND CLICKING ON THE &ldquo;AGREE&rdquo; OR
&ldquo;YES&rdquo; BUTTON DURING THE REGISTRATION PROCESS YOU AGREE
TO THIS AGREEMENT. IF YOU DO NOT AGREE TO THIS AGREEMENT DURING THE
REGISTRATION PROCESS, CLICK ON THE &ldquo;I DO NOT AGREE&rdquo; OR
&ldquo;NO&rdquo; BUTTON, AND MAKE NO FURTHER USE OF THE
SERVICE.</p>
<p>OPENROAD MAY IN ITS SOLE DISCRETION, BY POSTING A
REVISED AGREEMENT ON THE WEBSITE STATS.THOUGHTFARMER.COM, CHANGE
THE TERMS OF THIS AGREEMENT FROM TIME TO TIME AS IT RELATES TO YOUR
FUTURE USE OF THIS SERVICE. BY USING THE SERVICE AFTER THE REVISED
AGREEMENT HAS BEEN POSTED, YOU SIGNIFY YOUR ACCEPTANCE AND
AGREEMENT TO BE BOUND BY THE REVISED TERMS. YOU MAY NOT CHANGE
THESE TERMS IN ANY MANNER. EACH TIME YOU USE THE SERVICE YOU
SIGNIFY YOUR ACCEPTANCE AND AGREEMENT TO THE CURRENT VERSION OF
THIS AGREEMENT. IF YOU DO NOT AGREE WITH EACH PROVISION OF THIS
AGREEMENT, DO NOT USE THE SERVICE.</p>
<p>OpenRoad Communications Ltd. (&ldquo;OpenRoad&rdquo;)
provides its online data management service for ThoughtFarmer
intranet statistics commonly known as "Intranet Statistics"
(&ldquo;Service&rdquo;) to you (&ldquo;Subscriber&rdquo;) upon the
terms and conditions set out below. By using the Service,
Subscriber agrees to abide by the terms of this agreement
(&ldquo;Agreement&rdquo;).</p>

<p>1. &nbsp; <strong>License.</strong> Your right to use the Service is
subject to this Agreement and is made subject to the GNU General
Public License version 3 pursuant to which the software underlying
the Service is provided. OpenRoad may from time to time modify or
enhance the Service without notice to you. Your access to the
Service will be terminated without notice if your maintenance
account for OpenRoad's ThoughtFarmer software expires or is
otherwise terminated.</p>
<p>2. &nbsp; <strong>Subscriber's Account.</strong> Subscriber is
responsible for all access to the Service by Subscriber's personnel
or designated Users, whether or not Subscriber has knowledge of or
authorizes such use.</p>
<p>3. &nbsp; <strong>Subscriber&rsquo;s Internal Policies.</strong> OpenRoad
is not responsible for compliance with Subscriber&rsquo;s internal
policies, regardless of whether it has notice of them.</p>
<p>4. &nbsp; <strong>Term.</strong> OpenRoad may in its sole discretion
discontinue offering the Service at anytime without notice to
Subscriber.</p>
<p>5. &nbsp; <strong>Intellectual Property.</strong> ThoughtFarmer, the
ThoughtFarmer logo and other OpenRoad logos and product and service
names, including without limitation "Intranet Statistics" are
trademarks of OpenRoad (the &ldquo;OpenRoad Marks&rdquo;), whether
or not registered. Without OpenRoad`s prior permission, Subscriber
agrees not to display or use, in any manner, the OpenRoad
Marks.</p>
<p>6. &nbsp; <strong>Prohibited Uses.</strong> Subscriber agrees not to
access the Service by any means other than through the interface
that is provided by OpenRoad. Subscriber shall not access the
Service for the purpose of data mining or extracting content from
the Service beyond Subscriber&rsquo;s own data.</p>
<p>The Service has been designed so that each
Subscriber&rsquo;s data ("Subscriber's Data") can only be accessed
by that Subscriber (including Subscriber&rsquo;s Users). Subscriber
agrees that it will not attempt to access, download, copy or
otherwise use any information provided by the Service that does not
belong to Subscriber or that Subscriber is not authorized to
access, and Subscriber agrees to ensure that each individual User
authorized by Subscriber does not do so or attempt to do so. If,
however, Subscriber or any User authorized by Subscriber does
access, receive or otherwise obtain any such unauthorized
information, then Subscriber agrees to treat such information as
strictly confidential and promptly notify OpenRoad, and not to
download, copy, transmit or otherwise use any of such unauthorized
information, except as may be expressly authorized by
OpenRoad.</p>
<p>7. &nbsp; <strong>Security.</strong> OpenRoad will maintain the Service
at a reputable third party hosting facility, where commercially
reasonable security precautions are taken to prevent unauthorized
access to the Service. Subscriber acknowledges that,
notwithstanding such security precautions, use of, or connection to
the Internet provides the opportunity for unauthorized third
parties to circumvent such precautions and illegally gain access to
the Service and Subscriber&rsquo;s Data. ACCORDINGLY, OPENROAD
CANNOT AND DOES NOT GUARANTY THE PRIVACY, SECURITY, OR AUTHENTICITY
OF ANY INFORMATION SO TRANSMITTED OR STORED IN ANY SYSTEM CONNECTED
TO THE INTERNET.</p>
<p>8. &nbsp; <strong>Rights in Data.</strong> All property rights in the
Subscriber&rsquo;s Data that is provided by Subscriber, or by any
party authorized by Subscriber to submit data to the Service,
including without limitation copyrights, are and shall continue to
be the exclusive property of Subscriber. Subscriber acknowledges
and agrees that OpenRoad may disclose Subscriber&rsquo;s Data if
required to do so by law or with prior written consent of the
Subscriber. OpenRoad may provide statistical information, using
Subscriber&rsquo;s data, to third parties, but such information
will not include personally identifying information. OpenRoad may
access Subscriber&rsquo;s Data to respond to service or technical
problems with the Service.</p>
<p>OpenRoad shall retain Subscriber&rsquo;s Data for a
period of thirty (30) days after expiration or termination of this
Agreement. After thirty (30 days), OpenRoad may delete and destroy
all Subscriber&rsquo;s Data without notice or further liability to
the Subscriber.</p>
<p>OpenRoad reserves the right to establish (and notify
the Subscriber of) a maximum amount of memory or other computer
storage and a maximum amount of Subscriber&rsquo;s Data that
Subscriber may post, store, or transmit on or through the
Service.</p>
<p>9. &nbsp; <strong>Responsibility/Indemnity.</strong> Subscriber is solely
responsible for all access to the Service and use of the
Subscriber&rsquo;s Data by Subscriber&rsquo;s personnel or the use
of Subscriber&rsquo;s account, whether or not Subscriber has
knowledge of or authorizes such use. Subscriber and Users shall
maintain the confidentiality of password and account log-in
identification. Subscriber agrees to indemnify and hold harmless
OpenRoad against any liability or claim of any person that relates
to or principally caused by Subscriber's use of the Service.</p>
<p>Subscriber acknowledges that OpenRoad has no control
over the source, quality, format, nature, ownership or legality of
information submitted to the Service by the Subscriber or third
parties and that the Subscriber is responsible for any claims or
liabilities that may arise from the Subscriber&rsquo;s actions in
extracting or submitting information to the Service.</p>
<p>10. &nbsp; <strong>Warranty.</strong></p>
<p>OpenRoad warrants that:</p>
<p>(i) It has the power, authority and capacity, and has
received all necessary authorizations and approvals, to enter into
this Agreement, (ii) it owns or has all rights in and to the
intellectual property rights in the Service necessary to grant the
licenses granted in this Agreement, (iii) the use of the Service in
accordance with the terms of this Agreement does not , and will not
infringe on the intellectual property rights of a third party, (iv)
the Service will conform to the written descriptions that have been
provided to the Subscriber as are set out in the Schedules to this
Agreement and that are found at http://stats.thoughtfarmer.com/
at the date of this Agreement<strong>,</strong> (v) OpenRoad will
take all reasonable steps to ensure the Service will be free of
viruses, malicious codes and spy-ware throughout the term of this
Agreement, (vi) OpenRoad will undertake all reasonable efforts to
correct any material errors in the service.</p>
<p>11. &nbsp; <strong>Disclaimer and Limitation of Liability.</strong></p>
<p>EXCEPT AS PROVIDED IN THE WARRANTY ABOVE SUBSCRIBER
EXPRESSLY UNDERSTANDS AND AGREES THAT:</p>
<p>SUBSCRIBER&rsquo;S USE OF THE SERVICE IS AT
SUBSCRIBER&rsquo;S SOLE RISK. THE SERVICE IS PROVIDED ON AN
&ldquo;AS IS&rdquo; AND &ldquo;AS AVAILABLE&rdquo;
BASIS.</p>
<p>OPENROAD MAKES NO REPRESENTATION OR WARRANTY THAT (i)
THE SERVICE WILL MEET SUBSCRIBER&rsquo;S REQUIREMENTS, (ii) THE
SERVICE WILL BE UNINTERRUPTED, TIMELY, SECURE, OR ERROR-FREE, (iii)
THE RESULTS THAT MAY BE OBTAINED FROM THE USE OF THE SERVICE WILL
BE ACCURATE OR RELIABLE, (iv) THE PERFORMANCE OF THE INTERNET WILL
BE UNINTERRUPTED OR PERFORM AT SPECIFIED RATES, (v)
SUBSCRIBER&rsquo;S INTERNET SERVICE PROVIDER WILL PROVIDE
UNINTERRUPTED SERVICE OR PERFORM AT SPECIFIED RATES.</p>
<p>SUBSCRIBER EXPRESSLY UNDERSTANDS AND AGREES THAT
OPENROAD SHALL NOT BE LIABLE FOR ANY, INDIRECT, INCIDENTAL,
SPECIAL, CONSEQUENTIAL OR EXEMPLARY DAMAGES, INCLUDING BUT NOT
LIMITED TO, DAMAGES FOR LOSS OF REVENUES, PROFITS, GOODWILL, USE,
SUBSCRIBER&rsquo;S DATA, BODILY INJURY OR PROPERTY DAMAGE, FAILURE
TO REALIZE EXPECTED SAVINGS, OR OTHER INTANGIBLE LOSSES (EVEN IF
OPENROAD HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES),
RESULTING FROM: (i) THE USE OR THE INABILITY TO USE THE SERVICE;
(ii) INVALID DESTINATIONS, TRANSMISSION ERRORS, OR UNAUTHORIZED
ACCESS TO OR ALTERATION OF SUBSCRIBER&rsquo;S TRANSMISSIONS OR
SUBSCRIBER&rsquo;S DATA.</p>
<p>NO CLAIM, REGARDLESS OF FORM, MAY BE MADE OR ACTION
BROUGHT BY EITHER PARTY MORE THAN ONE YEAR AFTER THE BASIS FOR THE
CLAIM BECOMES KNOWN TO THE PARTY ASSERTING IT.</p>
<p>WITHOUT LIMITING THE GENERALITY OF THE FOREGOING,
OPENROAD LIABILITY TO SUBSCRIBER PURSUANT TO THIS AGREEMENT OR
PURSUANT TO ANY OTHER LEGAL THEORY, INCLUDING LEGAL FEES AND
AWARDABLE COSTS, SHALL NEVER EXCEED THE AMOUNT PAID BY SUBSCRIBER
TO OPENROAD IN THE PREVIOUS TWELVE MONTHS FOR THE USE OF THE
SERVICE .</p>
<p>12. &nbsp; <strong>Termination for Breach.</strong> Upon the occurrence of
any of the following events: (i) the other party materially
breaches or defaults in any of the material terms or conditions of
this Agreement, (ii) the other party makes any assignment for the
benefit of creditors, is insolvent or unable to pay its debts as
they mature in the ordinary course of business, or (iii) any
proceedings are instituted by or against the other party in
bankruptcy or under any insolvency laws or for reorganization,
receivership or dissolution, or (iv) if Subscriber`s license to use
the ThoughtFarmer software is terminated, then the non-defaulting
party may give the other party written notice of such default and
an opportunity to cure the default within thirty (30) days after
receipt of such notice, failing which the non-defaulting party may
cancel this Agreement without notice.</p>
<p>13. &nbsp; <strong>Publicity.</strong> OpenRoad may use Subscriber&rsquo;s
name as part of a general list of customers and may refer to
Subscriber as a user of the Service in general advertising and
marketing materials. Each party shall obtain the other&rsquo;s
permission prior to using the other party&rsquo;s name for any
other marketing or promotional purposes. The parties agree that any
press release or other public comments issued by either party
relating to this agreement will be prepared jointly between
OpenRoad and the Subscriber.</p>
<p>14. &nbsp; <strong>Force Majeure.</strong> Neither party is liable for any
delay, interruption or failure in the performance of its
obligations if caused by acts of God, war (declared or undeclared),
fire, flood, storm, slide, earthquake, power failure, inability to
obtain equipment, supplies or other facilities not caused by a
failure to pay, labour disputes, or other similar event beyond the
control of the party affected which may prevent or delay such
performance. If any such act or event occurs or is likely to occur,
the party affected shall promptly notify the other, giving
particulars of the event. The party so affected shall use
reasonable efforts to eliminate or remedy the event.</p>
<p>15. &nbsp; <strong>Notices.</strong> All notices required to be given to
OpenRoad shall be given to OpenRoad as set out in the Schedule A.
Any notice required to be given by OpenRoad may be given by e-mail
to the address of Subscriber&rsquo;s Technical and Administrative
Contact.</p>
<p>16. &nbsp; <strong>Counterparts/Facsimile.</strong> This agreement may be
executed in two counterparts, each of which will be deemed to be an
original, and both of which together shall constitute one
agreement. This Agreement may be executed via facsimile.</p>
<p>17. &nbsp; <strong>Sole Agreement.</strong> This Agreement constitutes the
sole agreement between the parties with respect to the
Service.</p>
<p>18. &nbsp; <strong>Governing Law.</strong> The laws of the British
Columbia govern this Agreement and all disputes arising out of it
shall be submitted to a court of competent jurisdiction in British
Columbia.</p>
<p>19. &nbsp; <strong>Assignment.</strong> This Agreement may not be assigned
by Subscriber.</p>
<p>20. &nbsp; <strong>General Provisions.</strong> No waiver of any of the
provisions of this Agreement shall be deemed to constitute a waiver
of any other provision nor shall such a waiver constitute a
continuing waiver unless otherwise expressly provided in writing
duly executed by the party to be bound. This Agreement is binding
upon the successors to and permitted assigns of the parties.</p>
</div>
<p class="termsButtonsContainer">
<input value="I Agree" tabindex="-1" id="acceptButton" type="button" class="termsButton" />
<input value="I Do Not Agree" tabindex="-1" id="dontAcceptButton" type="button" class="termsButton" />

</p>
</div>
{/literal}
