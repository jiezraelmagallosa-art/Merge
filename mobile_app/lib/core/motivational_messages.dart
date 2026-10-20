// SBC Internship Attendance System - Motivational Messages
import 'dart:math';
import 'package:flutter/material.dart';

class MotivationalMessages {
  static const List<String> timeInMessages = [
    "Magandang araw! Let's make today productive and meaningful!",
    "Aba, maaga at handa na! Keep up the good energy!",
    "Don't be late! Great start to your day, keep that momentum going!",
    "Smile! You're going to achieve great things today.",
    "New day, new learnings. I-galing mo mamaya!",
    "Mabuhay ang pursugidong estudyante! Aja!",
    "Focus, commit, and succeed. Ready ka na dyan!",
    "Laging dalhin ang ngiti at talino. Have a great shift ahead!",
    "Erased ang antok, ready na mag-work! Let's go!",
    "A brilliant day starts with an early arrival. Good job!",
    "Rise and shine! May you conquer all your tasks today.",
    "Erase the doubts, unleash your potential! Let's start the shift right.",
    "Napakagandang simula! Ipagpatuloy ang kasipagan.",
    "Araw mo na naman para magningning sa SBC! Good luck!",
    "A step closer to your dreams. Let's make today count!",
    "Pumasok na may ngiti, magtrabaho nang may puso.",
    "Be unstoppable today! Kayanin ang bawat hamon.",
    "Ready, set, grow! Marami ka na namang matututunan ngayon.",
    "Ang maagang estudyante ay laging pinagpala. Padayon!",
    "Let your passion drive your performance today!",
    "Welcome back! Handang-handa na ang iyong workspace para sa'yo.",
    "Keep your head up and your goals clear. Good shift!",
    "Every minute counts. I-maximize ang araw na ito!",
    "A positive mindset brings positive output. Good morning/afternoon!",
    "Wow, early bird! Saludo sa dedikasyon mo.",
    "Trust the process and enjoy your shift today!",
    "Araw ng mga champions ngayon. I-galing mo!",
    "Let your hard work speak for itself today.",
    "Tuwing papasok ka, may bago kang natututunan. Go lang ng go!",
    "Fresh start, fresh energy. Kaya mo 'yan!",
    "Isang hakbang paakyat sa tagumpay. Simulan na natin!",
    "Smile, breathe, and start your shift with confidence.",
    "Hindi hadlang ang pagod sa pusong pursigido. Aja!",
    "Araw para ipakita ang galing mo. I-enjoy ang shift!",
    "Sipag at tiyaga ang puhunan. Let's get to work!",
    "Welcome sa isa pang produktibong araw sa SBC!",
    "Oras na para gumawa ng magandang marka ngayon.",
    "Ang galing ng timing mo! Tuloy-tuloy lang ang swerte.",
    "Ready to learn, ready to lead. Let's do this!",
    "Ipakita ang tunay na lakas ng isang estudyante ng SBC.",
    "May pag-asa sa taong masipag. Magandang shift sa'yo!",
    "Keep your focus sharp and your heart positive.",
    "Ngayon ang tamang araw para mag-level up!",
    "Handa nang sumabak? Kasama mo ang iyong husay!",
    "Huwag kalimutang ngumiti habang nagtatrabaho.",
    "Ang sipag mo naman! Keep inspiring others.",
    "Tagumpay ang naghihintay sa mga hindi sumusuko.",
    "I-buo ang araw na ito ng may magagandang accomplishment!",
    "Araw ng tagumpay ngayon. Simulan na natin!",
    "Salamat sa dedikasyon. Let's rock this shift!"
  ];

  static const List<String> afternoonTimeOutMessages = [
    "Good work today! Rest well and see you tomorrow.",
    "Job well done! Ingat sa pag-uwi, deserve mo ang pahinga!",
    "Awtomatikong tapos na ang shift! Great hustle today!",
    "Amazing work! Time to recharge for another productive day.",
    "Isa na namang matagumpay na araw sa SBC! Saludo sa sipag mo.",
    "Mission accomplished! Uwi na at mag-relax.",
    "Proud of your dedication today. Rest up!",
    "Great job finishing your tasks! Ingat sa biyahe pauwi.",
    "Na-survive mo ang araw na ito nang may galing. See you next time!",
    "Isa kang mabuting halimbawa ng masipag na estudyante. Rest well!",
    "Oras na para magpahinga. You earned this break!",
    "Napakahusay ng ipinakita mo ngayong araw. Good job!",
    "Tapos na ang laban para sa araw na ito. Time to chill!",
    "Ingat sa pag-uwi! Bukas ulit para sa panibagong tagumpay.",
    "Isang produktibong araw na naman ang natapos mo. I'm proud of you!",
    "Bilib ako sa sipag mo ngayon. Magpahinga nang mahimbing!",
    "Done for the day! Pwede nang i-relax ang isip at katawan.",
    "Isa nanamang check sa iyong checklist. Mahusay!",
    "Saludo sa tiyaga mo buong araw. Hanggang sa susunod!",
    "Time to log out and unwind. Deserve mo ang masarap na tulog.",
    "Ang galing ng performance mo kanina! Magpahinga na.",
    "Nagbunga ang pagod mo ngayon. Uwi na at maghapunan!",
    "Isa kang rockstar ngayong araw! Paalam at ingat.",
    "Hanggang dito na muna. Ingat sa pag-uwi, student!",
    "Magandang pahinga sa'yo! You did an amazing job.",
    "Balik-enerhiya bukas! Ginalingan mo sobra ngayon.",
    "Na-i-pasa mo ang araw na ito nang may flying colors!",
    "Oras na para i-off ang pc at i-on ang relaxation mode.",
    "Proud moments lang ang peg natin ngayon. Uwi na!",
    "Napagod ka man, pero marami ka namang natutunan. Good job!",
    "Mag-ingat sa daan pauwi. Bukas uli!",
    "Isang malaking 'Thumbs Up' para sasipag mo ngayon!",
    "Tapos na ang shift, oras na para sa paborito mong libangan.",
    "Iba ka talaga magtrabaho! Pahinga na, idol.",
    "Salamat sa tulong at sipag mo ngayong araw na ito.",
    "Huwag kalimutang kumain at magpahinga pagkauwi.",
    "Kahanga-hanga ang dedikasyon mo. See you tomorrow!",
    "Done and dusted! Ang galing mo today.",
    "Makakatulog ka nang mahimbing dahil nagawa mo nang maayos ang trabaho mo.",
    "Ang husay mo! Hanggang sa susunod nating shift.",
    "Uwi na, i-treat ang sarili dahil masipag ka ngayon!",
    "Napawi ang pagod sa ganda ng performance mo.",
    "Siguraduhing mag-hydrate at magpahinga pagkauwi, ha?",
    "Isa kang patunay na ang sipag ay nagbubunga ng tagumpay.",
    "Tapos na ang hirap, oras na para sa sarili. Ingat!",
    "Walang tapon sa ginawa mo ngayon. Excellent work!",
    "I-lock na ang system at i-lock na rin ang stress. Uwi na!",
    "Hanggang bukas muli! Ipagpatuloy ang magandang ugali na ito.",
    "Superb energy today! Paalam at mag-ingat sa biyahe.",
    "Pagod na katawan, pero masayang isipan. Good job today!"
  ];

  static const List<String> timeOutMessages = afternoonTimeOutMessages;

  static const List<String> morningTimeOutMessages = [
    "Magandang tanghali! Kain nang maayos at mag-recharge para sa hapon. Let's keep the energy high!",
    "Good job ngayong umaga! I-enjoy ang lunch break at maghanda para sa mas productive pang hapon.",
    "Halfway there! Magpahinga sandali, mag-lunch, at balik tayong may bagong lakas mamayang hapon.",
    "Kain na! Tapos na ang umaga, kaya mo pang galingan lalo mamayang hapon. Aja!",
    "Magandang pahinga sa umaga! Mag-refuel para mas ganahan sa afternoon shift.",
    "Isang malaking 'Good Job' sa umagang ito! Sulitin ang pahinga para handa sa hapon.",
    "Reset, recharge, and get ready for a great afternoon ahead. Ingat sa lunch!",
    "Magandang tanghali! Ang ganda ng simula mo kanina, ituloy lang natin mamayang hapon.",
    "Time for a well-deserved lunch break. See you back here mamaya para sa afternoon grind!",
    "Umalis nang may ngiti dahil maganda ang umaga mo. Kain na at balik sa hapon!",
    "Napakagaling ng performance mo kanina! Mag-lunch na para may energy ulit mamaya.",
    "Dagdag na lakas para sa hapon! Kumain nang sapat at magpahinga saglit.",
    "Isang umagang punong-puno ng tagumpay. Relax muna, sabay hataw ulit mamayang hapon!",
    "Great morning shift! Let's conquer the afternoon just as brilliantly.",
    "I-enjoy ang break time! Kitakits ulit mamaya para sa tuloy-tuloy na tagumpay.",
    "Mahusay ang ginawa mo kanina. Mag-recharge para mas ganahan sa hapon!",
    "Pamper yourself with a good meal. Ready na ba ang hapon session mo?",
    "Pa-log out muna sa umaga, pero ready na magbabalik mamaya! Kain po.",
    "Ang bilis dumaan ng umaga dahil sa sipag mo. Enjoy your lunch break!",
    "Keep that momentum going! Kumain at magpahinga para handa sa hapon.",
    "Isang hakbang na lang, matatapos na ang buong araw. Lunch muna!",
    "Ang sipag mo kanina! Deserve mo ang masarap na tanghalian. See you later!",
    "Refresh your mind, fill your tummy, and get ready for the afternoon sprint.",
    "Magandang tanghali! Mag-relax habang kumakain para fresh pagbalik mamaya.",
    "50% done for the day! Rest well para sa huling yugto ng shift.",
    "Saludo sa sipag mo ngayong umaga! Kain na para may panggatong sa hapon.",
    "Tapos na ang umagang may ngiti. Magpahinga para sa masayang hapon!",
    "Magandang break time! Balik agad mamaya ha, miss ka na ng tasks mo.",
    "You crushed the morning shift! Time to recharge for round two.",
    "I-alis ang antok sa pamamagitan ng masarap na lunch. Aja sa hapon!",
    "Ginalingan mo kanina! Ituloy ang ganyang energy mamayang hapon.",
    "Mabilis na lumipas ang oras dahil focused ka. Kain na at pahinga saglit!",
    "Isang produktibong umaga! Mag-refuel na para sa afternoon productivity.",
    "Tanghalian na! Magpahinga nang maigi para lalo pang tumaas ang galing mo mamaya.",
    "Great job this morning! See you on the flip side (afternoon shift)!",
    "Pahinga muna ang utak at mata. Kain na para ready sa afternoon tasks.",
    "Ang ganda ng output mo kanina. Keep it up hanggang mamayang hapon!",
    "Sipag at tiyaga ang puhunan. Enjoy your lunch break and see you later!",
    "Tapos na ang umaga, kaya let's welcome the afternoon with high hopes!",
    "Mag-ingat sa pag-lunch! Magbalik nang may ngiti at sigla mamaya.",
    "Halfway to the finish line! Rest up and get ready for the afternoon rush.",
    "Napagod ka man kanina, worth it naman! Kain na para sa hapon.",
    "Excellent morning work! I-enjoy ang break at maghanda sa hapon.",
    "Keep shining! Mag-lunch na para hindi mapagod ang bida ngayong hapon.",
    "Isa kang magandang halimbawa ng masipag na estudyante. See you later!",
    "Relax, eat well, and prepare to conquer the afternoon shift.",
    "Umagang puno ng aral at gawa. Pahinga muna para mas lalong gumaling mamaya!",
    "Done with the morning block! Mag-recharge na para sa hapon.",
    "Magandang tanghali! Magpahinga nang sapat para handa na ulit sumabak mamaya."
  ];

  static const List<String> completionMessages = [
    "🎉 Great job! You have verified your attendance for today's CIT event!",
    "🏆 Event Attendance Recorded! CIT and your Dean acknowledge your active participation!",
    "🌟 Saludo sa iyong dedikasyon! Active and verified for this department event.",
    "🎓 Attendance Confirmed! Thank you for participating in today's CIT assembly!"
  ];

  static String getRandomCompletionMessage() {
    final rand = Random();
    return completionMessages[rand.nextInt(completionMessages.length)];
  }


  static bool checkIsTimeIn(String action) {
    final lower = action.toLowerCase();
    return !lower.contains('out');
  }

  static bool checkIsMorningOut(String action) {
    final lower = action.toLowerCase();
    final isOut = lower.contains('out');
    final isMorning = lower.contains('morning') || lower.contains('midday') || lower.contains('lunch') || lower.contains('am');
    return isOut && isMorning;
  }

  static bool checkIsAfternoonOut(String action) {
    final lower = action.toLowerCase();
    final isOut = lower.contains('out');
    final isAfternoon = lower.contains('afternoon') || lower.contains('pm');
    return isOut && isAfternoon;
  }

  static String getRandomMessage(String action) {
    final rand = Random();
    if (checkIsTimeIn(action)) {
      return timeInMessages[rand.nextInt(timeInMessages.length)];
    } else if (checkIsMorningOut(action)) {
      return morningTimeOutMessages[rand.nextInt(morningTimeOutMessages.length)];
    } else {
      return afternoonTimeOutMessages[rand.nextInt(afternoonTimeOutMessages.length)];
    }
  }

  static void showMotivationalDialog(
    BuildContext context, {
    required String actionType,
    required String message,
    String? recordedTime,
  }) {
    final isTimeIn = checkIsTimeIn(actionType);
    final displayMsg = message.isNotEmpty ? message : getRandomMessage(actionType);


    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isTimeIn
                      ? const Color(0xFFFFF8E1)
                      : const Color(0xFFE8F5E9),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  isTimeIn ? Icons.wb_sunny_rounded : Icons.task_alt_rounded,
                  size: 44,
                  color: isTimeIn ? const Color(0xFFFFB800) : const Color(0xFF2E7D32),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                isTimeIn ? 'Time-In Recorded!' : 'Time-Out Recorded!',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0F172A),
                ),
              ),
              if (recordedTime != null && recordedTime.isNotEmpty) ...[
                const SizedBox(height: 6),
                Text(
                  'Logged at $recordedTime',
                  style: TextStyle(
                    fontSize: 13,
                    color: Colors.grey.shade600,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
              const SizedBox(height: 20),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFFF4F6F9),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: const Color(0xFF0F172A).withValues(alpha: 0.12),
                  ),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(
                      Icons.format_quote_rounded,
                      color: Color(0xFFFFB800),
                      size: 28,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        displayMsg,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF0F172A),
                          height: 1.35,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0F172A),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  onPressed: () => Navigator.pop(context),
                  child: Text(
                    isTimeIn ? "Let's Do This! 🚀" : 'Enjoy Your Rest! 🌟',
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  static void showGoalCompletionDialog(
    BuildContext context, {
    String? message,
  }) {
    final displayMsg = (message != null && message.isNotEmpty)
        ? message
        : getRandomCompletionMessage();

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(18),
                decoration: const BoxDecoration(
                  color: Color(0xFFFFF8E1),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.emoji_events_rounded,
                  size: 54,
                  color: Color(0xFFFFB800),
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                '🎉 Congratulations! 🏆',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Required Goal Hours Completed!',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFFFFB800),
                ),
              ),
              const SizedBox(height: 18),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFFF4F6F9),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: const Color(0xFFFFB800).withValues(alpha: 0.4),
                  ),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Icon(
                      Icons.stars_rounded,
                      color: Color(0xFFFFB800),
                      size: 26,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        displayMsg,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF0F172A),
                          height: 1.38,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 22),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0F172A),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  onPressed: () => Navigator.pop(context),
                  child: const Text(
                    'Celebrate & Continue! 🎓✨',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

