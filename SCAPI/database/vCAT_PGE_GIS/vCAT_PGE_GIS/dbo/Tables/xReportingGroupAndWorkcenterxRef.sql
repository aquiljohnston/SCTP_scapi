CREATE TABLE [dbo].[xReportingGroupAndWorkcenterxRef] (
    [ReportingGroupAndWorkCenterID] INT           IDENTITY (1, 1) NOT NULL,
    [ReportingGroupUID]             VARCHAR (100) NULL,
    [WorkCenterUID]                 VARCHAR (100) NULL,
    [CreatedUserUID]                VARCHAR (100) NULL,
    [ModifiedUserUID]               VARCHAR (100) NULL,
    [CreateDatetime]                DATETIME      CONSTRAINT [DFxReportingGroupAndDivisionxRefCreateDatetime] DEFAULT (getdate()) NULL,
    [ModifiedDatetime]              DATETIME      NULL,
    [Revision]                      INT           CONSTRAINT [DFxReportingGroupAndDivisionxRefRevision] DEFAULT ((0)) NULL,
    [ActiveFlag]                    BIT           CONSTRAINT [DFxReportingGroupAndDivisionxRefActiveFlag] DEFAULT ((1)) NULL
);

