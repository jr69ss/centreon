import { GraphOptionId } from '../Graph/Performance/models';
import {
  Status,
  Acknowledgement,
  Downtime,
  Parent,
  ResourceLinks,
  NamedEntity,
} from '../models';

import { StoredCustomTimePeriod, TimePeriodId } from './tabs/Graph/models';

export interface ResourceDetails extends NamedEntity {
  acknowledged: boolean;
  acknowledgement?: Acknowledgement;
  active_checks: boolean;
  alias?: string;
  calculation_type?: string;
  command_line?: string;
  downtimes: Array<Downtime>;
  duration: string;
  execution_time: number;
  flapping: boolean;
  fqdn?: string;
  groups?: Array<NamedEntity>;
  information: string;
  last_check: string;
  last_notification: string;
  last_status_change: string;
  latency: number;
  links: ResourceLinks;
  monitoring_server_name?: string;
  next_check: string;
  notification_number: number;
  parent: Parent;
  percent_state_change: number;
  performance_data?: string;
  severity_level: number;
  status: Status;
  timezone?: string;
  tries: string;
  type: 'service' | 'host' | 'metaservice';
  uuid: string;
}

interface GraphOption {
  id: GraphOptionId;
  label: string;
  value: boolean;
}

export interface GraphOptions {
  [GraphOptionId.displayEvents]: GraphOption;
}

export interface GraphTabParameters {
  graphOptions?: GraphOptions;
  selectedCustomTimePeriod?: StoredCustomTimePeriod;
  selectedTimePeriodId?: TimePeriodId;
}

export interface ServicesTabParameters {
  graphMode: boolean;
  graphTimePeriod: GraphTabParameters;
}

export interface TabParameters {
  graph?: GraphTabParameters;
  services?: ServicesTabParameters;
}

export interface DetailsUrlQueryParameters {
  id: number;
  parentId?: number;
  parentType?: string;
  tab?: string;
  tabParameters?: TabParameters;
  type: string;
  uuid: string;
}
