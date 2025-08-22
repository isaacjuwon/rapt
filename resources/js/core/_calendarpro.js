import { Calendar } from 'vanilla-calendar-pro'
import {
    parseDates,
    getDateString,
    getDate,
    getWeekNumber,
} from 'vanilla-calendar-pro/utils'

export default function (Alpine) {
    Alpine.magic(
        'calendar',
        () =>
            (el, options = null) =>
                new Calendar(el, options),
    )
    Alpine.magic('calendarUtils', () => ({
        parseDates,
        getDateString,
        getDate,
        getWeekNumber,
    }))
}
