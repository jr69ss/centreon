import * as React from 'react';

import { Skeleton } from '@material-ui/lab';
import { makeStyles } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  skeletons: {
    display: 'grid',
    gridGap: theme.spacing(1),
  },
  skeleton: {
    transform: 'none',
    height: 62,
    width: '100%',
  },
}));

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  const serviceLoadingSkeleton = <Skeleton className={classes.skeleton} />;

  return (
    <div className={classes.skeletons}>
      {serviceLoadingSkeleton}
      {serviceLoadingSkeleton}
      {serviceLoadingSkeleton}
    </div>
  );
};

export default LoadingSkeleton;
