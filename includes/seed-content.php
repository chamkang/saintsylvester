<?php
/**
 * Canonical seed data: the real medical team and the service page content.
 * Used by setup.php on fresh installs. Schedules are placeholders pending
 * confirmation of real consultation days by the clinic.
 */
return [

'doctors' => [
    // slug, name, ONMC, spec_fr, spec_en, bio_fr, bio_en, languages, photo, services[], schedules[ [weekday,start,end,slot] ]
    ['dr-akwa-john', 'Dr. Akwa John', '4529',
     'Gynécologue-Obstétricien · Fondateur', 'Obstetrician-Gynecologist · Founder',
     'Gynécologue-obstétricien fort de près de 30 ans de pratique, le Dr Akwa a fondé la Saint Sylvester Medical Foundation en 2016. Il suit personnellement les dossiers de fertilité, du premier bilan jusqu\'à la naissance.',
     'Obstetrician-gynecologist with nearly 30 years of practice, Dr Akwa founded Saint Sylvester Medical Foundation in 2016. He personally follows fertility files, from the first work-up to birth.',
     'Français, English', 'assets/img/team/doctor-1.jpg',
     ['fertility','gynecology','antenatal','surgery'],
     [[1,'08:00','14:00',30],[3,'08:00','14:00',30],[5,'08:00','14:00',30]]],
    ['dr-ayameria', 'Dr. Ayamena Assiene', '7559',
     'Gynécologue-Obstétricien', 'Obstetrician-Gynecologist',
     'Gynécologue-obstétricien, le Dr Ayamena assure les consultations gynécologiques, le suivi de grossesse, les consultations prénatales et l\'accompagnement en fertilité.',
     'Obstetrician-gynecologist, Dr Ayamena provides gynecological consultations, pregnancy follow-up, antenatal care and fertility support.',
     'Français, English', 'assets/img/team/doctor-2.jpg',
     ['fertility','gynecology','antenatal'],
     [[2,'08:00','14:00',30],[4,'08:00','14:00',30],[6,'08:00','13:00',30]]],
    ['dr-yemene', 'Dr. Yemene Zangue', '7078',
     'Interniste & Cardiologue', 'Internist & Cardiologist',
     'Interniste et cardiologue, le Dr Yemene prend en charge l\'hypertension, le diabète et les maladies du cœur, avec un suivi rigoureux des patients chroniques.',
     'Internist and cardiologist, Dr Yemene manages hypertension, diabetes and heart conditions, with close follow-up of chronic patients.',
     'Français, English', 'assets/img/team/doctor-3.jpg',
     ['internal-medicine'],
     [[2,'09:00','15:00',30],[4,'09:00','15:00',30]]],
    ['dr-engama', 'Dr. Engama Ebong', '7675',
     'Pédiatre', 'Pediatrician',
     'Pédiatre, le Dr Engama veille sur la croissance, les vaccinations et la santé des nourrissons, des enfants et des adolescents.',
     'Pediatrician, Dr Engama looks after the growth, vaccinations and health of infants, children and adolescents.',
     'Français, English', 'assets/img/team/doctor-4.jpg',
     ['pediatrics'],
     [[1,'08:30','12:30',20],[2,'08:30','12:30',20],[3,'08:30','12:30',20],[4,'08:30','12:30',20],[5,'08:30','12:30',20]]],
    ['dr-tchatchouang', 'Dr. Tchatchouang Lowe', '10076',
     'Médecin généraliste', 'General Practitioner',
     'Médecin généraliste, le Dr Tchatchouang est le premier interlocuteur des familles et coordonne les examens de laboratoire et d\'imagerie.',
     'General practitioner, Dr Tchatchouang is the first point of care for families and coordinates laboratory and imaging exams.',
     'Français, English', '',
     ['general-medicine','imaging','laboratory'],
     [[1,'08:00','16:00',20],[2,'08:00','16:00',20],[3,'08:00','16:00',20],[4,'08:00','16:00',20],[5,'08:00','16:00',20],[6,'08:00','14:00',20]]],
],

// slug => [body_fr, body_en, features_fr (pipe-separated), features_en]
'service_content' => [
'fertility' => [
 "Le centre de fertilité est le cœur de la Saint Sylvester Medical Foundation. Nous accompagnons les couples qui attendent un enfant depuis des mois ou des années, avec un parcours clair : consultation initiale en toute discrétion, bilan complet du couple, puis plan de traitement personnalisé.\n\nChaque dossier est suivi personnellement par un gynécologue-obstétricien. Le laboratoire et l'échographie sur place permettent de réaliser la quasi-totalité des examens sans quitter la clinique — et la confidentialité de votre parcours est absolue.",
 "The fertility center is the heart of Saint Sylvester Medical Foundation. We support couples who have been hoping for a child for months or years, with a clear pathway: a discreet initial consultation, a complete couple work-up, then a personalised treatment plan.\n\nEvery file is personally followed by an obstetrician-gynecologist. Our in-house laboratory and ultrasound mean almost every exam happens without leaving the clinic — and the confidentiality of your journey is absolute.",
 "Bilan de fertilité complet du couple|Suivi de l'ovulation par échographie|Spermogramme et bilan hormonal sur place|Plan de traitement personnalisé|Accompagnement jusqu'à la grossesse et au-delà",
 "Complete couple fertility work-up|Ultrasound ovulation monitoring|On-site semen analysis and hormone panel|Personalised treatment plan|Support through pregnancy and beyond"],
'gynecology' => [
 "De la première consultation gynécologique au suivi complet de la grossesse et à l'accouchement, nos gynécologues-obstétriciens veillent sur la santé de la femme à chaque étape de la vie.\n\nConsultations, dépistages, échographies obstétricales et prise en charge chirurgicale lorsque nécessaire : tout se fait sur place, dans le respect, l'écoute et la confidentialité.",
 "From a first gynecological consultation to full pregnancy follow-up and delivery, our obstetrician-gynecologists watch over women's health at every stage of life.\n\nConsultations, screenings, obstetric ultrasounds and surgical care when needed: everything happens on site, with respect, attentiveness and confidentiality.",
 "Consultations gynécologiques et dépistages|Suivi de grossesse complet|Échographie obstétricale sur place|Planification familiale et conseil|Prise en charge de l'accouchement",
 "Gynecological consultations and screenings|Complete pregnancy follow-up|On-site obstetric ultrasound|Family planning and counselling|Delivery care"],
'antenatal' => [
 "La consultation prénatale (CPN) est le rendez-vous le plus important de votre grossesse. Un suivi régulier permet de surveiller la santé de la maman et du bébé, de dépister tôt les complications et de préparer sereinement l'accouchement.\n\nNotre équipe vous reçoit à chaque étape : pesée, tension, examens de laboratoire, échographies et conseils de nutrition — avec un carnet de suivi tenu à jour à chaque visite.",
 "The antenatal visit is the most important appointment of your pregnancy. Regular follow-up monitors mother and baby, detects complications early and prepares for a safe delivery.\n\nOur team welcomes you at every stage: weight, blood pressure, laboratory tests, ultrasounds and nutrition advice — with your follow-up booklet updated at every visit.",
 "Suivi mensuel de la grossesse|Dépistage et prévention des complications|Échographies de croissance|Vaccination et supplémentation|Préparation à l'accouchement",
 "Monthly pregnancy follow-up|Screening and prevention of complications|Growth ultrasounds|Vaccination and supplements|Birth preparation"],
'general-medicine' => [
 "La médecine générale est la porte d'entrée de la fondation : fièvre, paludisme, infections, douleurs, bilans de santé — notre médecin généraliste reçoit toute la famille, sans distinction d'âge.\n\nAprès l'examen, les analyses et l'imagerie se font sur place, et si votre état nécessite un spécialiste, vous êtes orienté immédiatement vers le bon médecin de l'équipe.",
 "General medicine is the foundation's front door: fever, malaria, infections, pain, health check-ups — our general practitioner sees the whole family, at any age.\n\nAfter the examination, tests and imaging happen on site, and if your condition needs a specialist you are referred immediately to the right doctor on the team.",
 "Consultations toutes affections courantes|Dépistage et traitement du paludisme|Bilans de santé complets|Orientation rapide vers nos spécialistes|Suivi des maladies chroniques",
 "Consultations for all common conditions|Malaria testing and treatment|Complete health check-ups|Fast referral to our specialists|Chronic disease follow-up"],
'internal-medicine' => [
 "Hypertension, diabète, maladies du cœur : les maladies chroniques se soignent d'autant mieux qu'elles sont suivies régulièrement. Notre interniste-cardiologue assure le diagnostic, le traitement et le suivi au long cours.\n\nÉlectrocardiogramme, bilan cardiovasculaire et analyses de laboratoire se font sur place, avec un plan de traitement expliqué clairement et réévalué à chaque visite.",
 "Hypertension, diabetes, heart disease: chronic conditions are best managed with regular follow-up. Our internist-cardiologist provides diagnosis, treatment and long-term care.\n\nElectrocardiogram, cardiovascular work-up and laboratory tests are done on site, with a treatment plan explained clearly and reviewed at every visit.",
 "Suivi de l'hypertension et du diabète|Consultations de cardiologie|Électrocardiogramme (ECG)|Bilan cardiovasculaire complet|Éducation et prévention",
 "Hypertension and diabetes follow-up|Cardiology consultations|Electrocardiogram (ECG)|Complete cardiovascular work-up|Patient education and prevention"],
'pediatrics' => [
 "Du nouveau-né à l'adolescent, notre pédiatre veille sur la santé de vos enfants : consultations, suivi de la croissance, vaccinations et prise en charge des maladies de l'enfance.\n\nChaque visite est l'occasion de faire le point sur le développement, l'alimentation et le calendrier vaccinal — dans un cadre rassurant pour l'enfant comme pour les parents.",
 "From newborn to teenager, our pediatrician watches over your children's health: consultations, growth monitoring, vaccinations and treatment of childhood illnesses.\n\nEvery visit is a chance to review development, nutrition and the vaccination schedule — in a setting that reassures both child and parents.",
 "Consultations pédiatriques|Suivi de la croissance et du développement|Vaccinations selon le calendrier national|Prise en charge des maladies de l'enfance|Conseils de nutrition infantile",
 "Pediatric consultations|Growth and development monitoring|Vaccinations per the national schedule|Treatment of childhood illnesses|Child nutrition advice"],
'surgery' => [
 "Notre bloc opératoire équipé permet de réaliser les interventions chirurgicales programmées — notamment gynécologiques et obstétricales — dans des conditions de sécurité et d'asepsie rigoureuses.\n\nChaque intervention est précédée d'une consultation pré-opératoire complète et suivie d'une surveillance post-opératoire attentive, jusqu'au rétablissement complet.",
 "Our equipped operating theatre handles scheduled surgical procedures — notably gynecological and obstetric — under strict safety and asepsis standards.\n\nEvery procedure is preceded by a complete pre-operative consultation and followed by attentive post-operative monitoring until full recovery.",
 "Chirurgie gynécologique et obstétricale|Césariennes programmées et d'urgence|Consultation pré-opératoire complète|Surveillance post-opératoire|Hospitalisation de courte durée",
 "Gynecological and obstetric surgery|Scheduled and emergency C-sections|Complete pre-operative consultation|Post-operative monitoring|Short-stay hospitalisation"],
'imaging' => [
 "Radiographies et échographies se font directement à la clinique, sans rendez-vous extérieur ni perte de temps. Les images sont interprétées par nos médecins et intégrées immédiatement à votre dossier.\n\nL'échographie obstétricale accompagne chaque étape de la grossesse, et l'imagerie générale appuie le diagnostic de nos consultations et de la chirurgie.",
 "X-rays and ultrasound scans happen directly at the clinic — no outside appointment, no wasted time. Images are read by our physicians and added to your file immediately.\n\nObstetric ultrasound accompanies every stage of pregnancy, and general imaging supports diagnosis across our consultations and surgery.",
 "Échographie obstétricale et gynécologique|Échographie abdominale et générale|Radiographie standard|Résultats interprétés sur place|Images intégrées à votre dossier",
 "Obstetric and gynecological ultrasound|Abdominal and general ultrasound|Standard X-ray|Results read on site|Images added to your file"],
'laboratory' => [
 "Notre laboratoire d'analyses réalise sur place les examens courants et spécialisés : hématologie, biochimie, sérologie, parasitologie et bilans hormonaux — y compris les bilans de fertilité.\n\nLes prélèvements se font à la clinique et la plupart des résultats sont disponibles le jour même, transmis directement à votre médecin pour une prise en charge sans délai.",
 "Our laboratory performs routine and specialised tests on site: hematology, biochemistry, serology, parasitology and hormone panels — including fertility work-ups.\n\nSamples are taken at the clinic and most results are available the same day, sent directly to your doctor so treatment starts without delay.",
 "Hématologie et biochimie|Sérologie et parasitologie|Bilans hormonaux et de fertilité|Prélèvements sur place|Résultats le jour même pour la plupart des examens",
 "Hematology and biochemistry|Serology and parasitology|Hormone and fertility panels|On-site sample collection|Same-day results for most tests"],
],
];
