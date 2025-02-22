<template>
  <div id="month-aggregate" class="mx-0 mx-lg-auto">
    <b-table-lite
      :caption="capture"
      caption-top
      :fields="fields"
      :items="items"
      bordered
    />
    <b-table-lite
      class="mx-4"
      caption="Kalenderwochen:"
      caption-top
      :fields="weekFields"
      :items="weekItems"
      bordered
    />
  </div>
</template>

<script lang="ts">
import { Component, Vue } from "vue-property-decorator";
import { Carry, Saldo, WorkingMonth } from "@/models";
import { timesConfig } from "@/configs";
import { mapState } from "vuex";
import { GermanKwService } from "@/services";

@Component({
  computed: {
    ...mapState("workingTime", ["carryResult", "month", "previous"]),
  },
})
export default class MonthAggregate extends Vue {
  carryResult!: Carry;
  month!: WorkingMonth;
  previous!: WorkingMonth;
  fields = [
    {
      key: "key",
      label: "",
      class: "first_column",
    },
    {
      key: "carryResult",
      label: "Übertrag",
    },
    {
      key: "month",
      label: "Laufender Monat",
    },
    {
      key: "total",
      label: "Gesamt",
    },
  ];

  get items() {
    let carryResult = {
      key: "Saldo",
      carryResult: this.carryResult.saldo
        ? this.carryResult.saldo.toString()
        : "",
      month: this.$store.getters["workingTime/saldo"],
      total: this.$store.getters["workingTime/saldoTotal"],
    };
    let holidayResult = {
      key: "Erholungsurlaub",
      carryResult: this.holidaysLeftString,
      month: this.month.takenHolidays,
      total: this.holidaysTotalString,
    };
    if (this.carryResult.hasMissing) {
      carryResult.carryResult = "Unbekannt";
      carryResult.total = carryResult.month;
      holidayResult.carryResult = "Unbekannt";
      holidayResult.total = holidayResult.month;
    }
    return [carryResult, holidayResult];
  }

  get weekFields() {
    const weekFields: { key: string; label: string; class?: string }[] = [];
    const kWs = this.kWs;
    weekFields.push({
      key: "key",
      label: "",
      class: "first_column",
    });
    kWs.forEach((kw) => {
      weekFields.push({
        key: "" + kw,
        label: "KW " + kw,
      });
    });
    return weekFields;
  }

  get weekItems() {
    interface LooseObj {
      [key: string]: string;
    }
    const total: LooseObj = {};
    total.key = "Gesamt";
    this.weekFields.forEach((field) => {
      if (field.key !== "key") {
        total[field.key] = this.getTotalForKW(
          Number.parseInt(field.key)
        ).toString(false);
      }
    });
    const mobile: LooseObj = {};
    mobile.key = "Mobiles Arbeiten";
    this.weekFields.forEach((field) => {
      if (field.key !== "key") {
        mobile[field.key] = this.getMobileForKW(
          Number.parseInt(field.key)
        ).toString(false);
      }
    });
    const share: LooseObj = {};
    share.key = "Anteil";
    this.weekFields.forEach((field) => {
      if (field.key !== "key") {
        share[field.key] =
          this.getShareForKW(Number.parseInt(field.key)).toFixed(2) + " %";
      }
    });
    return [total, mobile, share];
  }

  get holidaysLeftString() {
    return this.month.monthNumber <= timesConfig.previousHolidaysValidTo
      ? this.carryResult.holidays +
          " (Resturlaub: " +
          this.carryResult.holidaysPrevious +
          ")"
      : this.carryResult.holidays;
  }

  get holidaysTotalString() {
    let holidays = this.carryResult.holidays;
    let taken = this.month.takenHolidays;
    if (this.month.monthNumber <= timesConfig.previousHolidaysValidTo) {
      let holidaysPrevious = this.carryResult.holidaysPrevious;
      if (holidaysPrevious >= taken) {
        holidaysPrevious -= taken;
      } else {
        taken -= holidaysPrevious;
        holidays -= taken;
      }
      return holidays + " (Resturlaub: " + holidaysPrevious + ")";
    }
    return holidays - taken;
  }

  get capture() {
    let capture = "Zusammenfassung: ";
    if (this.carryResult.hasMissing) {
      let missing = this.carryResult.missing;
      capture += "(Eine Darstellung des Übertrags ist nicht möglich, denn ";
      if (missing.length === 1) {
        capture += "der Monat " + missing[0] + " ist ";
      } else {
        capture += "die Monate ";
        missing.forEach((missed, index) => {
          if (index + 1 === missing.length) {
            capture += " und ";
          }
          capture += missed;
          if (index + 3 <= missing.length) {
            capture += ", ";
          }
        });
        capture += " sind ";
      }
      capture += "noch nicht abgeschlossen.)";
    }
    return capture;
  }

  get kWs(): number[] {
    const firstOfMonth = new Date(this.month.monthDate);
    firstOfMonth.setDate(1);
    const lastOfMonth = new Date(this.month.monthDate);
    lastOfMonth.setMonth(lastOfMonth.getMonth() + 1);
    lastOfMonth.setDate(0);
    let firstKw = GermanKwService.getGermanKW(firstOfMonth);
    let lastKw = GermanKwService.getGermanKW(lastOfMonth);
    if (GermanKwService.getGermanDay(firstOfMonth) >= 5) {
      if (firstKw >= 52) firstKw = 1;
      else firstKw++;
    }
    if (GermanKwService.getGermanDay(lastOfMonth) < 4) lastKw--;
    const result = [];
    if (firstKw >= 52) {
      result.push(firstKw);
      firstKw = 1;
    }
    for (let i = firstKw; i <= lastKw; i++) {
      result.push(i);
    }
    return result;
  }

  private getTotalForKW(kw: number) {
    const monthTotal = this.month.days
      .filter((day) => day.calendarWeek === kw)
      .map((day) => day.actualTime)
      .reduce(
        (sum, dayTime) => (dayTime ? Saldo.getSum(dayTime, sum!) : sum),
        new Saldo(0)
      )!;
    const prevTotal = this.previous.days
      .filter((day) => day.calendarWeek === kw)
      .map((day) => day.actualTime)
      .reduce(
        (sum, dayTime) => (dayTime ? Saldo.getSum(dayTime, sum!) : sum),
        new Saldo(0)
      )!;
    return Saldo.getSum(monthTotal, prevTotal);
  }

  private getMobileForKW(kw: number) {
    const monthMobile = this.month.days
      .filter((day) => day.calendarWeek === kw && day.mobileWorking)
      .map((day) => day.actualTime)
      .reduce(
        (sum, dayTime) => (dayTime ? Saldo.getSum(dayTime, sum!) : sum),
        new Saldo(0)
      )!;
    const prevMobile = this.previous.days
      .filter((day) => day.calendarWeek === kw && day.mobileWorking)
      .map((day) => day.actualTime)
      .reduce(
        (sum, dayTime) => (dayTime ? Saldo.getSum(dayTime, sum!) : sum),
        new Saldo(0)
      )!;
    return Saldo.getSum(monthMobile, prevMobile);
  }

  private getShareForKW(kw: number) {
    return this.getTotalForKW(kw).getMillis()
      ? Saldo.getPercentage(this.getTotalForKW(kw), this.getMobileForKW(kw))
      : 0;
  }
}
</script>

<!--suppress CssUnusedSymbol -->
<style scoped>
#month-aggregate {
  text-align: center;
  width: 90%;
  overflow: hidden;
}

table {
  width: 650px;
  font-size: larger;
  float: left;
}

/deep/ table caption {
  font-weight: bold;
  color: inherit;
}

/deep/ .first_column {
  font-weight: bold;
}

/deep/ table td {
  text-align: center;
  vertical-align: middle;
}

/deep/ table th {
  text-align: center;
}
</style>
