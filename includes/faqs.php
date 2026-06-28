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

        'gynecology' => [
            'en' => [
                ['When should I see a gynaecologist?',
                 'An annual check-up is a good habit. See us sooner for pelvic pain, abnormal bleeding, menstrual problems, or for contraception and family-planning advice.'],
                ['What gynaecological services do you offer?',
                 'Consultations and screening, pelvic and obstetric ultrasound on site, family planning and counselling, pregnancy follow-up, and surgical care when it is needed.'],
                ['Will my consultation be confidential?',
                 'Yes. Every consultation is private and treated with discretion and respect.'],
                ['How do I book, and what does it cost?',
                 'Book a consultation online or call us. The consultation fee is 10,000 FCFA, payable at the clinic.'],
            ],
            'fr' => [
                ['Quand consulter un gynécologue ?',
                 'Une visite annuelle est une bonne habitude. Consultez plus tôt en cas de douleurs pelviennes, saignements anormaux, troubles des règles, ou pour la contraception et le planning familial.'],
                ['Quels services de gynécologie proposez-vous ?',
                 'Consultations et dépistage, échographie pelvienne et obstétricale sur place, planning familial et conseil, suivi de grossesse, et prise en charge chirurgicale si nécessaire.'],
                ['Ma consultation est-elle confidentielle ?',
                 'Oui. Chaque consultation est privée et traitée avec discrétion et respect.'],
                ['Comment prendre rendez-vous, et quel est le coût ?',
                 'Prenez rendez-vous en ligne ou appelez-nous. Les frais de consultation sont de 10 000 FCFA, à régler à la clinique.'],
            ],
        ],

        'antenatal' => [
            'en' => [
                ['When should I start antenatal care?',
                 'As soon as you know you are pregnant. Early visits confirm the pregnancy, check your health and plan a safe follow-up.'],
                ['What happens during antenatal visits?',
                 'Regular check-ups of you and your baby — blood pressure, weight, obstetric ultrasound and the screening tests recommended at each stage of pregnancy.'],
                ['Do you have ultrasound on site?',
                 'Yes. Obstetric ultrasound is done at the clinic, so most checks happen without you leaving.'],
                ['How often will I have appointments?',
                 'Usually monthly early on, then more frequently as you approach your due date — your doctor sets the schedule for your pregnancy.'],
            ],
            'fr' => [
                ['Quand commencer le suivi prénatal ?',
                 'Dès que vous savez que vous êtes enceinte. Les premières visites confirment la grossesse, vérifient votre santé et organisent un suivi en toute sécurité.'],
                ['Comment se déroulent les consultations prénatales ?',
                 'Un suivi régulier de vous et de votre bébé — tension, poids, échographie obstétricale et les examens de dépistage recommandés à chaque étape de la grossesse.'],
                ['L\'échographie est-elle disponible sur place ?',
                 'Oui. L\'échographie obstétricale se fait à la clinique, la plupart des contrôles se font donc sans vous déplacer.'],
                ['À quelle fréquence les rendez-vous ?',
                 'Généralement mensuels au début, puis plus fréquents à l\'approche du terme — votre médecin fixe le calendrier selon votre grossesse.'],
            ],
        ],

        'general-medicine' => [
            'en' => [
                ['What does a general medicine consultation cover?',
                 'Everyday health concerns for the whole family — infections, fevers, aches, check-ups and the first assessment of any new symptom. Your GP is your first point of care.'],
                ['When should I see a GP rather than a specialist?',
                 'Start with the GP for most problems. They diagnose and treat common conditions and refer you to the right specialist when needed.'],
                ['Do you handle lab tests and referrals?',
                 'Yes. The GP coordinates your laboratory and imaging exams on site and follows up on the results with you.'],
                ['How do I book, and what does it cost?',
                 'Book online or call us. The consultation fee is 10,000 FCFA, payable at the clinic.'],
            ],
            'fr' => [
                ['Que couvre une consultation de médecine générale ?',
                 'Les problèmes de santé courants de toute la famille — infections, fièvres, douleurs, bilans et la première évaluation de tout nouveau symptôme. Votre médecin généraliste est votre premier interlocuteur.'],
                ['Quand voir un généraliste plutôt qu\'un spécialiste ?',
                 'Commencez par le généraliste pour la plupart des problèmes. Il diagnostique et traite les affections courantes et vous oriente vers le bon spécialiste si nécessaire.'],
                ['Gérez-vous les analyses et les orientations ?',
                 'Oui. Le généraliste coordonne vos examens de laboratoire et d\'imagerie sur place et assure le suivi des résultats avec vous.'],
                ['Comment prendre rendez-vous, et quel est le coût ?',
                 'En ligne ou par téléphone. Les frais de consultation sont de 10 000 FCFA, à régler à la clinique.'],
            ],
        ],

        'internal-medicine' => [
            'en' => [
                ['What does an internist treat?',
                 'Adult and chronic conditions — high blood pressure, diabetes, heart disease and other long-term illnesses — with close, ongoing follow-up.'],
                ['When should I see an internist or cardiologist?',
                 'If you have hypertension, diabetes, a heart condition or persistent symptoms that need specialist follow-up, or if your GP refers you.'],
                ['Do you follow chronic patients long-term?',
                 'Yes. We provide rigorous, continuous follow-up so your condition stays well controlled over time.'],
                ['How do I book, and what does it cost?',
                 'Book online or call us. The internist consultation fee is 15,000 FCFA, payable at the clinic.'],
            ],
            'fr' => [
                ['Que traite un interniste ?',
                 'Les pathologies de l\'adulte et chroniques — hypertension, diabète, maladies du cœur et autres maladies de longue durée — avec un suivi rapproché et continu.'],
                ['Quand consulter un interniste ou un cardiologue ?',
                 'En cas d\'hypertension, de diabète, d\'une maladie cardiaque, ou de symptômes persistants nécessitant un suivi spécialisé, ou sur orientation de votre généraliste.'],
                ['Suivez-vous les patients chroniques sur le long terme ?',
                 'Oui. Nous assurons un suivi rigoureux et continu pour que votre état reste bien contrôlé dans le temps.'],
                ['Comment prendre rendez-vous, et quel est le coût ?',
                 'En ligne ou par téléphone. Les frais de consultation chez l\'interniste sont de 15 000 FCFA, à régler à la clinique.'],
            ],
        ],

        'pediatrics' => [
            'en' => [
                ['What ages do you care for?',
                 'From newborns through childhood to adolescence — your child\'s growth, development and health at every stage.'],
                ['Do you offer vaccinations and growth monitoring?',
                 'Yes. We follow your child\'s growth and development and provide the recommended vaccinations.'],
                ['When should I bring my child in?',
                 'For routine check-ups and vaccines, and whenever your child has a fever, a feeding or growth concern, or any worrying symptom.'],
                ['How do I book, and what does it cost?',
                 'Book online or call us. The consultation fee is 10,000 FCFA, payable at the clinic.'],
            ],
            'fr' => [
                ['Quels âges prenez-vous en charge ?',
                 'Du nouveau-né à l\'adolescence, en passant par l\'enfance — la croissance, le développement et la santé de votre enfant à chaque étape.'],
                ['Proposez-vous les vaccinations et le suivi de croissance ?',
                 'Oui. Nous suivons la croissance et le développement de votre enfant et assurons les vaccinations recommandées.'],
                ['Quand amener mon enfant ?',
                 'Pour les visites de routine et les vaccins, et dès que votre enfant présente une fièvre, un problème d\'alimentation ou de croissance, ou tout symptôme inquiétant.'],
                ['Comment prendre rendez-vous, et quel est le coût ?',
                 'En ligne ou par téléphone. Les frais de consultation sont de 10 000 FCFA, à régler à la clinique.'],
            ],
        ],

        'surgery' => [
            'en' => [
                ['What surgical procedures do you offer?',
                 'Gynaecological and obstetric surgery and other procedures within our specialties, performed when your doctor judges it necessary.'],
                ['Does surgery happen at the clinic?',
                 'Yes, eligible procedures are carried out on site. Your doctor explains the plan, preparation and recovery beforehand.'],
                ['How is surgery arranged?',
                 'It always starts with a consultation and assessment. If surgery is needed, the team schedules it and guides you through every step.'],
                ['How do I get started?',
                 'Book a consultation or call us, and the doctor will advise on the right course of care.'],
            ],
            'fr' => [
                ['Quelles interventions chirurgicales proposez-vous ?',
                 'La chirurgie gynécologique et obstétricale et d\'autres interventions relevant de nos spécialités, réalisées lorsque votre médecin le juge nécessaire.'],
                ['La chirurgie se fait-elle à la clinique ?',
                 'Oui, les interventions éligibles sont réalisées sur place. Votre médecin vous explique au préalable le déroulement, la préparation et la convalescence.'],
                ['Comment s\'organise une intervention ?',
                 'Tout commence par une consultation et un bilan. Si une chirurgie est nécessaire, l\'équipe la programme et vous accompagne à chaque étape.'],
                ['Comment commencer ?',
                 'Prenez rendez-vous ou appelez-nous, et le médecin vous conseillera sur la prise en charge adaptée.'],
            ],
        ],

        'imaging' => [
            'en' => [
                ['What imaging do you offer?',
                 'Ultrasound (echography), including obstetric, pelvic and abdominal scans, performed on site.'],
                ['Do I need a referral for imaging?',
                 'A doctor\'s request is usually best so the right exam is done — your GP or specialist at the clinic can arrange it.'],
                ['When will I get my results?',
                 'Most ultrasound results are available quickly, often during the same visit, and are explained to you by the doctor.'],
                ['How do I arrange an exam?',
                 'Call us or book a consultation, and we will organise the imaging you need.'],
            ],
            'fr' => [
                ['Quels examens d\'imagerie proposez-vous ?',
                 'L\'échographie, notamment obstétricale, pelvienne et abdominale, réalisée sur place.'],
                ['Faut-il une ordonnance pour l\'imagerie ?',
                 'Une demande médicale est généralement préférable pour réaliser le bon examen — votre généraliste ou spécialiste à la clinique peut l\'organiser.'],
                ['Quand aurai-je mes résultats ?',
                 'La plupart des résultats d\'échographie sont disponibles rapidement, souvent lors de la même visite, et vous sont expliqués par le médecin.'],
                ['Comment organiser un examen ?',
                 'Appelez-nous ou prenez rendez-vous, et nous organiserons l\'imagerie nécessaire.'],
            ],
        ],

        'laboratory' => [
            'en' => [
                ['What laboratory tests do you offer?',
                 'A broad range on site — blood tests, hormone panels, semen analysis and the common diagnostic exams your doctor may request.'],
                ['Do I need an appointment for lab tests?',
                 'You can call us or come in; some tests need a doctor\'s request or specific preparation, which we will explain.'],
                ['Do I need to fast before a test?',
                 'Some tests (such as fasting blood sugar) require it — we will tell you how to prepare when you book.'],
                ['When will my results be ready?',
                 'Turnaround depends on the test; many are available within the day. We will let you know when to expect yours.'],
            ],
            'fr' => [
                ['Quels examens de laboratoire proposez-vous ?',
                 'Une large gamme sur place — analyses de sang, bilans hormonaux, spermogramme et les examens de diagnostic courants demandés par votre médecin.'],
                ['Faut-il un rendez-vous pour les analyses ?',
                 'Vous pouvez nous appeler ou passer ; certains examens nécessitent une demande médicale ou une préparation particulière, que nous vous expliquerons.'],
                ['Faut-il être à jeun avant un examen ?',
                 'Certains examens (comme la glycémie à jeun) l\'exigent — nous vous indiquerons comment vous préparer lors de la prise de rendez-vous.'],
                ['Quand mes résultats seront-ils prêts ?',
                 'Le délai dépend de l\'examen ; beaucoup sont disponibles dans la journée. Nous vous indiquerons quand attendre les vôtres.'],
            ],
        ],

    ];

    return $faqs[$slug][$lang] ?? ($faqs[$slug]['en'] ?? []);
}
