import numeral from 'numeral';
import { isNil } from 'ramda';

interface FormatMetricValueProps {
  base?: number;
  unit: string;
  value: number | null;
}

const formatMetricValue = ({
  value,
  unit,
  base = 1000,
}: FormatMetricValueProps): string | null => {
  if (isNil(value)) {
    return null;
  }

  const base2Units = [
    'B',
    'bytes',
    'bytespersecond',
    'B/s',
    'B/sec',
    'o',
    'octets',
    'b/s',
    'b',
  ];

  const base1024 = base2Units.includes(unit) || Number(base) === 1024;

  const formatSuffix = base1024 ? ' ib' : 'a';

  return numeral(value)
    .format(`0.[00]${formatSuffix}`)
    .replace(/\s|i|B/g, '');
};

export default formatMetricValue;
