import { CancelToken } from 'axios';

import {
  buildListingEndpoint,
  getData,
  ListingModel,
  postData,
  putData,
  deleteData,
  patchData,
} from '@centreon/ui';

import { baseEndpoint } from '../../api/endpoint';
import { Filter } from '../models';

const filterEndpoint = `${baseEndpoint}/users/filters/events-view`;

interface ListCustomFiltersProps {
  limit: number;
  page: number;
}

const buildListCustomFiltersEndpoint = (
  parameters: ListCustomFiltersProps,
): string =>
  buildListingEndpoint({
    baseEndpoint: filterEndpoint,
    parameters,
  });

const listCustomFilters = (cancelToken: CancelToken) => (): Promise<
  ListingModel<Filter>
> =>
  getData<ListingModel<Filter>>(cancelToken)(
    buildListCustomFiltersEndpoint({ limit: 100, page: 1 }),
  );

type FilterWithoutId = Omit<Filter, 'id'>;

const createFilter = (cancelToken: CancelToken) => (
  filter: FilterWithoutId,
): Promise<Filter> => {
  return postData<FilterWithoutId, Filter>(cancelToken)({
    endpoint: filterEndpoint,
    data: filter,
  });
};

interface UpdateFilterProps {
  id: number;
  filter: FilterWithoutId;
}

const updateFilter = (cancelToken: CancelToken) => (
  parameters: UpdateFilterProps,
): Promise<Filter> => {
  return putData<FilterWithoutId, Filter>(cancelToken)({
    endpoint: `${filterEndpoint}/${parameters.id}`,
    data: parameters.filter,
  });
};

interface PatchFilterProps {
  id?: number;
  order: number;
}

const patchFilter = (cancelToken: CancelToken) => (
  parameters: PatchFilterProps,
): Promise<Filter> => {
  return patchData<PatchFilterProps, Filter>(cancelToken)({
    endpoint: `${filterEndpoint}/${parameters.id}`,
    data: { order: parameters.order },
  });
};

const deleteFilter = (cancelToken: CancelToken) => (parameters: {
  id: number;
}): Promise<void> => {
  return deleteData<void>(cancelToken)(`${filterEndpoint}/${parameters.id}`);
};

export {
  filterEndpoint,
  listCustomFilters,
  buildListCustomFiltersEndpoint,
  createFilter,
  updateFilter,
  patchFilter,
  deleteFilter,
};
