﻿CREATE TABLE [dbo].[rgMapGridLog_OLD] (
    [rgMapGridID]            INT              IDENTITY (1, 1) NOT NULL,
    [MapGridsUID]            VARCHAR (100)    NOT NULL,
    [ProjectID]              INT              NOT NULL,
    [SourceID]               VARCHAR (50)     NOT NULL,
    [CreatedUserUID]         VARCHAR (100)    NOT NULL,
    [ModifiedUserUID]        VARCHAR (100)    NOT NULL,
    [CreateDTLT]             DATETIME         NOT NULL,
    [ModifiedDTLT]           DATETIME         NOT NULL,
    [Comments]               VARCHAR (2000)   NULL,
    [RevisionComments]       VARCHAR (500)    NULL,
    [Revision]               INT              NOT NULL,
    [ActiveFlag]             BIT              NOT NULL,
    [StatusType]             VARCHAR (200)    NOT NULL,
    [SAPObjectID]            VARCHAR (100)    NULL,
    [Division]               VARCHAR (200)    NULL,
    [District]               VARCHAR (200)    NULL,
    [OfficeCode]             VARCHAR (200)    NULL,
    [WorkCenter]             VARCHAR (200)    NULL,
    [County]                 VARCHAR (200)    NULL,
    [WallMap]                VARCHAR (50)     NULL,
    [MapNumber]              VARCHAR (50)     NULL,
    [PlatNumber]             VARCHAR (50)     NULL,
    [PlatMapNumber]          VARCHAR (50)     NULL,
    [WorkCenterAbbreviation] VARCHAR (4)      NULL,
    [FLOC]                   VARCHAR (200)    NULL,
    [FuncLocMWC]             VARCHAR (4)      NULL,
    [FuncLocMapBoundary]     VARCHAR (4)      NULL,
    [FuncLocPlatSuffix]      CHAR (1)         NULL,
    [FuncLocMap]             VARCHAR (4)      NULL,
    [FuncLocPlat]            VARCHAR (4)      NULL,
    [FuncLocPlatChar1]       CHAR (1)         NULL,
    [FuncLocPlatChar2]       CHAR (1)         NULL,
    [FuncLocPlatChar3]       CHAR (1)         NULL,
    [FuncLocPlatChar4]       CHAR (1)         NULL,
    [CentroidLat]            FLOAT (53)       NULL,
    [CentroidLong]           FLOAT (53)       NULL,
    [TotalVertices]          INT              NULL,
    [MaxDistance]            FLOAT (53)       NULL,
    [GeoBufferWithDrift]     INT              NULL,
    [LastLeakSurvey]         DATETIME         NULL,
    [ScheduledSurvey]        DATETIME         NULL,
    [LeakSurveyFrequency]    VARCHAR (50)     NULL,
    [Last6MoSurveyDate1]     DATETIME         NULL,
    [Next6MoSurveyDate1]     DATETIME         NULL,
    [Last6MoSurveyDate2]     DATETIME         NULL,
    [Next6MoSurveyDate2]     DATETIME         NULL,
    [Last1YrSurveyDate]      DATETIME         NULL,
    [Next1YrSurveyDate]      DATETIME         NULL,
    [Last3YrSurveyDate]      DATETIME         NULL,
    [Next3YrSurveyDate]      DATETIME         NULL,
    [ObjectID]               INT              NULL,
    [SHAPE]                  [sys].[geometry] NULL
);

