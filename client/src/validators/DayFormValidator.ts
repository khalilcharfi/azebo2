import { Carry, Holiday, WorkingDay, WorkingMonth } from "@/models";
import { timeOffsConfig, timesConfig } from "@/configs";

export default class DayFormValidator {
  private day: WorkingDay;
  private readonly holidays: Holiday[];
  private readonly carryResult: Carry;
  private readonly month: WorkingMonth;
  public errors: string[] = [];

  constructor(
    day: WorkingDay,
    holidays: Holiday[],
    carryResult: Carry,
    month: WorkingMonth
  ) {
    this.day = day;
    this.holidays = holidays;
    this.carryResult = carryResult;
    this.month = month;
  }

  /**
   * Validates the whole form.
   *
   * Should be called on each blur event of each form field.
   */
  validate(): string[] {
    this.errors = [];
    this.beginAfterEnd();
    this.timeOffWithBeginAndEnd();
    this.moreThanTenHours();
    this.inCoreTime();
    this.isWorkingDay();
    this.negativeRestOfTakenHolidays();
    return this.errors;
  }

  /**
   * Tests if both, begun and end are filled in, whether the beginning time is
   * before the end time.
   */
  beginAfterEnd() {
    if (!this.day.isEndAfterBegin()) {
      this.errors.push("Das Ende der Arbeitszeit muss nach dem Beginn liegen!");
    }
  }

  /**
   * Tests whether there are (superfluous) beginning or end times, if a
   * 'Time Off' is given.
   */
  timeOffWithBeginAndEnd() {
    if (!this.day.validateTimeOffWithBeginEnd()) {
      this.errors.push(
        `Bei Verwendung der Bemerkung \u201E${
          timeOffsConfig.find((value) => value.value === this.day.timeOff)!.text
        }" darf kein Arbeitsbeginn und -ende angegeben werden!`
      );
    }
  }

  /**
   * Tests whether the working day had more than ten working hours.
   */
  moreThanTenHours() {
    if (this.day.isMoreThanTenHours()) {
      if (this.day.timeOff !== "lang") {
        this.errors.push(
          "Arbeitstage mit mehr als zehn Stunden Arbeitszeit müssen die " +
            'Bemerkung \u201Eüberlanger Arbeitstag" erhalten'
        );
      }
    } else {
      if (this.day.timeOff === "lang") {
        this.errors.push(
          'Die Bemerkung \u201Eüberlanger Arbeitstag" kann nur vergeben werden,' +
            ' falls \u201EAnfang" und \u201EEnde" gesetzt sind und die Ist-Zeit mehr ' +
            "als zehn Stunden beträgt"
        );
      }
    }
  }

  /**
   * Tests whether the core time is covered by the working time.
   */
  inCoreTime() {
    if (
      this.day.isBeginAfterCore() &&
      !(this.day.timeOff === "ausgleich" || this.day.timeOff === "zusatz")
    ) {
      this.errors.push(
        "Der Beginn der Arbeitszeit darf nicht nach dem Beginn der" +
          ' Kernarbeitszeit liegen, oder es muss die Bemerkung \u201EZeitausgleich"' +
          " angegeben werden."
      );
    }
    if (
      this.day.isEndAfterCore(this.holidays) &&
      !(
        this.day.timeOff === "ausgleich" ||
        this.day.timeOff === "da_krank" ||
        this.day.timeOff === "da_befr" ||
        this.day.timeOff === "zusatz"
      )
    ) {
      this.errors.push(
        "Das Ende der Arbeitszeit darf nicht vor dem Ende der" +
          ' Kernarbeitszeit liegen, oder es muss die Bemerkung \u201EZeitausgleich"' +
          ' bzw. \u201EDienstabbruch" angegeben werden.'
      );
    }
    if (
      this.day.timeOff === "ausgleich" &&
      !(this.day.isBeginAfterCore() || this.day.isEndAfterCore(this.holidays))
    ) {
      this.errors.push(
        'Die Bemerkung \u201EZeitausgleich" ist nur zulässig, falls Arbeitsbeginn' +
          " oder -ende außerhalb der Kernarbeitszeit liegt."
      );
    }
    if (
      (this.day.timeOff === "da_krank" || this.day.timeOff === "da_befr") &&
      !this.day.isEndAfterCore(this.holidays)
    ) {
      this.errors.push(
        'Die Bemerkungen "Dienstabbruch (krank)" und' +
          '\u201EDienstabbruch (Dienstbefr.)" sind nur zulässig, falls das' +
          " Arbeitsende außerhalb der Kernarbeitszeit liegt."
      );
    }
  }

  /**
   * Tests whether this day is an actual working day.
   */
  isWorkingDay() {
    if (
      this.day.hasWorkingTime &&
      !this.day.isActualWorkingDay &&
      this.day.timeOff !== "zusatz"
    ) {
      this.errors.push(
        "An diesem Tag haben Sie keinen Arbeitstag. Falls Sie trotzdem" +
          " Arbeitszeiten eintragen, müssen Sie die Bemerkung" +
          ' \u201Ezusätzlicher Arbeitstag" hinzufügen.'
      );
    }
    if (this.day.isActualWorkingDay && this.day.timeOff === "zusatz") {
      this.errors.push(
        "An einem regulären Arbeitstag darf die Bemerkung" +
          ' \u201Ezusätzlicher Arbeitstag" nicht angegeben werden'
      );
    }
  }

  /**
   * Tests the 'Time Off' `urlaub`.
   */
  negativeRestOfTakenHolidays() {
    const remainingHolidays =
      this.month.monthNumber <= timesConfig.previousHolidaysValidTo
        ? this.carryResult.holidaysPrevious + this.carryResult.holidays
        : this.carryResult.holidays;
    if (
      remainingHolidays <= this.month.takenHolidays &&
      this.day.timeOff === "urlaub"
    ) {
      this.errors.push(
        'Sie können die Bemerkung \u201EUrlaub" nur eintragen,' +
          " wenn Sie noch über Urlaubstage verfügen."
      );
    }
    if (!this.day.isActualWorkingDay && this.day.timeOff === "urlaub") {
      this.errors.push(
        'Sie können die Bemerkung \u201EUrlaub" nur eintragen,' +
          " wenn Sie einen Arbeitstag haben."
      );
    }
  }
}
