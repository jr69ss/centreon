import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, path } from 'ramda';

import GraphIcon from '@material-ui/icons/BarChart';
import ListIcon from '@material-ui/icons/List';

import { useRequest, IconButton, ListingModel } from '@centreon/ui';

import { TabProps } from '..';
import { ResourceContext, useResourceContext } from '../../../Context';
import {
  labelSwitchToGraph,
  labelSwitchToList,
} from '../../../translatedLabels';
import { listResources } from '../../../Listing/api';
import { Resource } from '../../../models';
import InfiniteScroll from '../../InfiniteScroll';
import useTimePeriod from '../../../Graph/Performance/TimePeriods/useTimePeriod';
import TimePeriodButtonGroup from '../../../Graph/Performance/TimePeriods';
import { TimePeriodId } from '../Graph/models';
import memoizeComponent from '../../../memoizedComponent';

import ServiceGraphs from './Graphs';
import ServiceList from './List';
import LoadingSkeleton from './LoadingSkeleton';

type ServicesTabContentProps = TabProps &
  Pick<
    ResourceContext,
    'selectResource' | 'tabParameters' | 'setServicesTabParameters'
  >;

const ServicesTabContent = ({
  details,
  tabParameters,
  selectResource,
  setServicesTabParameters,
}: ServicesTabContentProps): JSX.Element => {
  const { t } = useTranslation();

  const [graphMode, setGraphMode] = React.useState<boolean>(
    tabParameters.services?.graphMode || false,
  );

  const [canDisplayGraphs, setCanDisplayGraphs] = React.useState(false);

  const {
    selectedTimePeriod,
    changeSelectedTimePeriod,
    periodQueryParameters,
    getIntervalDates,
  } = useTimePeriod({
    defaultSelectedTimePeriodId: path(
      ['services', 'selectedTimePeriodId'],
      tabParameters,
    ),
    onTimePeriodChange: (timePeriodId: TimePeriodId) => {
      setServicesTabParameters({
        graphMode,
        selectedTimePeriodId: timePeriodId,
      });
    },
  });

  const { sendRequest, sending } = useRequest({
    request: listResources,
  });

  const limit = graphMode ? 6 : 30;

  const sendListingRequest = ({
    atPage,
  }: {
    atPage?: number;
  }): Promise<ListingModel<Resource>> => {
    return sendRequest({
      limit,
      page: atPage,
      resourceTypes: ['service'],
      onlyWithPerformanceData: graphMode ? true : undefined,
      search: {
        conditions: [
          {
            field: 'h.name',
            values: {
              $eq: details?.name,
            },
          },
        ],
      },
    });
  };

  const switchMode = (): void => {
    setCanDisplayGraphs(false);
    const mode = !graphMode;

    setGraphMode(mode);

    setServicesTabParameters({
      graphMode: mode,
      selectedTimePeriodId: selectedTimePeriod.id,
    });
  };

  React.useEffect(() => {
    // To make sure that graphs are not displayed until 'entities' are reset
    setCanDisplayGraphs(true);
  }, [graphMode]);

  const labelSwitch = graphMode ? labelSwitchToList : labelSwitchToGraph;
  const switchIcon = graphMode ? <ListIcon /> : <GraphIcon />;

  const loading = isNil(details) || sending;

  return (
    <>
      <IconButton
        title={t(labelSwitch)}
        ariaLabel={t(labelSwitch)}
        disabled={loading}
        onClick={switchMode}
      >
        {switchIcon}
      </IconButton>
      <InfiniteScroll<Resource>
        preventReloadWhen={details?.type !== 'host'}
        sendListingRequest={sendListingRequest}
        details={details}
        loadingSkeleton={<LoadingSkeleton />}
        filter={
          graphMode ? (
            <TimePeriodButtonGroup
              selectedTimePeriodId={selectedTimePeriod.id}
              onChange={changeSelectedTimePeriod}
              disabled={loading}
            />
          ) : undefined
        }
        reloadDependencies={[graphMode]}
        loading={sending}
        limit={limit}
      >
        {({ infiniteScrollTriggerRef, entities }): JSX.Element => {
          const displayGraphs = graphMode && canDisplayGraphs;

          return displayGraphs ? (
            <ServiceGraphs
              services={entities}
              infiniteScrollTriggerRef={infiniteScrollTriggerRef}
              periodQueryParameters={periodQueryParameters}
              getIntervalDates={getIntervalDates}
              selectedTimePeriod={selectedTimePeriod}
            />
          ) : (
            <ServiceList
              services={entities}
              onSelectService={selectResource}
              infiniteScrollTriggerRef={infiniteScrollTriggerRef}
            />
          );
        }}
      </InfiniteScroll>
    </>
  );
};

const MemoizedServiceTabContent = memoizeComponent<ServicesTabContentProps>({
  memoProps: ['details', 'tabParameters'],
  Component: ServicesTabContent,
});

const ServicesTab = ({ details }: TabProps): JSX.Element => {
  const {
    selectResource,
    tabParameters,
    setServicesTabParameters,
  } = useResourceContext();

  return (
    <MemoizedServiceTabContent
      details={details}
      tabParameters={tabParameters}
      selectResource={selectResource}
      setServicesTabParameters={setServicesTabParameters}
    />
  );
};

export default ServicesTab;
