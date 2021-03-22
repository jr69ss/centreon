import { ListingModel } from '@centreon/ui';

export type ResourceType = 'host' | 'service' | 'metaservice';

export interface NamedEntity {
  uuid: string;
  id: number;
  name: string;
}

export interface Icon {
  url: string;
  name: string;
}

type ParentLinks = Pick<ResourceLinks, 'uris'>;

export interface Parent extends NamedEntity {
  icon?: Icon;
  status: Status;
  links?: ParentLinks;
  type: ResourceType;
}

export interface Status {
  severity_code: number;
  name: string;
}

export interface Resource extends NamedEntity {
  icon?: Icon;
  parent?: Parent;
  status: Status;
  links?: ResourceLinks;
  acknowledged?: boolean;
  in_downtime?: boolean;
  duration?: string;
  tries?: string;
  last_check?: string;
  information?: string;
  severity_level?: number;
  short_type: 'h' | 's' | 'm';
  type: ResourceType;
  passive_checks?: boolean;
}

export type ResourceListing = ListingModel<Resource>;

export interface Downtime {
  author_name: string;
  comment: string;
  entry_time: string;
  start_time: string;
  end_time: string;
}

export interface Acknowledgement {
  author_name: string;
  comment: string;
  entry_time: string;
  is_persistent: boolean;
  is_sticky: boolean;
}

export interface ResourceEndpoints {
  details?: string;
  performance_graph?: string;
  status_graph?: string;
  timeline?: string;
  acknowledgement?: string;
  downtime?: string;
  metrics?: string;
}

export interface ResourceUris {
  configuration?: string;
  logs?: string;
  reporting?: string;
}

export interface Notes {
  label?: string;
  url: string;
}

export interface ResourceExternals {
  action_url?: string;
  notes?: Notes;
}

export interface ResourceLinks {
  endpoints: ResourceEndpoints;
  uris: ResourceUris;
  externals: ResourceExternals;
}

export type TranslationType = (label: string) => string;

export type SortOrder = 'asc' | 'desc';
