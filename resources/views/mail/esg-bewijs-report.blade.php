<x-mail::message>
# Uw biodiversiteits-bewijs

Beste {{ $data['partner']['company'] }},

Hierbij ontvangt u het geverifieerde biodiversiteits-bewijs voor seizoen **{{ $data['report']['season'] }}** (rapport **{{ $data['report']['nr'] }}**).

In dit seizoen zijn **{{ $data['totals']['species'] }} soorten** en **{{ $data['totals']['birds'] }} vogels** vastgesteld op uw geadopteerde gebied **{{ $data['area']['name'] }}** — via Greide-scan, AI-herkenning en expertverificatie.

Het volledige rapport vindt u in de bijlage (PDF). U kunt dit document gebruiken als onderbouwing in uw duurzaamheidsverslag, jaarverslag en op social media.

<x-mail::panel>
Dit is een onderbouwend bewijs- en verhaaldocument, geen gecertificeerd CSRD-/ESRS-compliancerapport.
</x-mail::panel>

Met vriendelijke groet,<br>
{{ $brand }} · Agrarisch Natuurfonds Fryslân
</x-mail::message>
