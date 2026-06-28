<?php
/**
 * Per-service FAQs (question, answer), shown on the service page and emitted as
 * FAQPage structured data. Only services listed here get an FAQ block.
 */
function ssmf_service_faqs(string $slug, string $lang): array
{
    $faqs = [
        'fertility' => [
            'en' => [
                ['When should we see a fertility specialist?',
                 'You can consult after about 12 months of trying to conceive without success — or after 6 months if the woman is over 35, or sooner if you already know of issues such as irregular periods. An early assessment often saves time.'],
                ['Do you offer IVF and assisted reproduction (PMA)?',
                 'Yes. We guide couples through the whole fertility pathway — initial assessment, ovulation monitoring, hormone and semen analysis, and assisted-reproduction treatment — each file personally followed by an obstetrician-gynaecologist.'],
                ['What happens at the first consultation?',
                 'A discreet conversation about your history, then a complete work-up of the couple (ultrasound, hormone panel and semen analysis). The doctor then proposes a personalised treatment plan.'],
                ['Is the man examined too?',
                 'Yes — fertility involves both partners, so a semen analysis is part of every work-up and is done on site.'],
                ['Is my fertility journey kept confidential?',
                 'Completely. Confidentiality is absolute, and our in-house laboratory and ultrasound mean almost every exam is done without leaving the clinic.'],
                ['How do we start, and what does it cost?',
                 'Book a consultation online or call us. The consultation fee is 10,000 FCFA, payable at the clinic; your doctor will explain any treatment costs during the visit.'],
            ],
            'fr' => [
                ['Quand consulter un spécialiste de la fertilité ?',
                 'Vous pouvez consulter après environ 12 mois d\'essais sans succès — ou après 6 mois si la femme a plus de 35 ans, ou plus tôt en cas de troubles connus comme des règles irrégulières. Un bilan précoce fait souvent gagner du temps.'],
                ['Proposez-vous la FIV et la PMA ?',
                 'Oui. Nous accompagnons les couples sur tout le parcours de fertilité — bilan initial, suivi de l\'ovulation, bilan hormonal et spermogramme, et traitement de procréation assistée — chaque dossier étant suivi personnellement par un gynécologue-obstétricien.'],
                ['Comment se déroule la première consultation ?',
                 'Un échange discret sur votre histoire, puis un bilan complet du couple (échographie, bilan hormonal et spermogramme). Le médecin propose ensuite un plan de traitement personnalisé.'],
                ['L\'homme est-il aussi examiné ?',
                 'Oui — la fertilité concerne les deux partenaires : un spermogramme fait partie de chaque bilan et se réalise sur place.'],
                ['Mon parcours reste-t-il confidentiel ?',
                 'Totalement. La confidentialité est absolue, et notre laboratoire et notre échographie sur place permettent de réaliser la quasi-totalité des examens sans quitter la clinique.'],
                ['Comment commencer, et quel est le coût ?',
                 'Prenez rendez-vous en ligne ou appelez-nous. Les frais de consultation sont de 10 000 FCFA, à régler à la clinique ; votre médecin vous expliquera les éventuels coûts de traitement lors de la visite.'],
            ],
        ],
    ];

    return $faqs[$slug][$lang] ?? ($faqs[$slug]['en'] ?? []);
}
